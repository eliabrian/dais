<?php

use App\Models\Campaign;
use App\Models\Character;
use App\Models\System;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('campaigns index lists gm and player campaigns for authenticated user', function () {
    $user = User::factory()->create();
    $otherGm = User::factory()->create();

    $system = System::create([
        'name' => 'D&D 5.5E (2024) SRD',
        'code' => 'dnd5e_2024',
    ]);

    $gmCampaign = Campaign::create([
        'name' => 'Curse of Strahd',
        'description' => 'Gothic horror campaign',
        'gm_id' => $user->id,
        'invite_code' => 'STRAHD01',
        'status' => 'active',
        'system_id' => $system->id,
    ]);

    $playerCampaign = Campaign::create([
        'name' => 'Lost Mine of Phandelver',
        'description' => 'Starter adventure',
        'gm_id' => $otherGm->id,
        'invite_code' => 'PHAND01',
        'status' => 'active',
        'system_id' => $system->id,
    ]);

    $user->joinedCampaigns()->attach($playerCampaign->id);

    $response = $this->actingAs($user)->get(route('campaigns.index'));

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Campaigns/Index')
            ->has('gmCampaigns', 1)
            ->where('gmCampaigns.0.id', $gmCampaign->id)
            ->has('playerCampaigns', 1)
            ->where('playerCampaigns.0.id', $playerCampaign->id)
        );
});

test('user can create a campaign defaulting to dnd5e_2024', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('campaigns.store'), [
        'name' => 'Shadows of Phandalin',
        'description' => 'A 2024 5.5e adventure',
    ]);

    $campaign = Campaign::where('gm_id', $user->id)->first();
    expect($campaign)->not->toBeNull();

    $response->assertRedirect(route('campaigns.show', $campaign));
});

test('user can join a campaign via invite code', function () {
    $gm = User::factory()->create();
    $player = User::factory()->create();

    $system = System::create([
        'name' => 'D&D 5.5E (2024) SRD',
        'code' => 'dnd5e_2024',
    ]);

    $campaign = Campaign::create([
        'name' => 'Dragonlance',
        'description' => 'War of the Lance',
        'gm_id' => $gm->id,
        'invite_code' => 'DRAGON01',
        'status' => 'active',
        'system_id' => $system->id,
    ]);

    $response = $this->actingAs($player)->post(route('campaigns.join'), [
        'invite_code' => 'dragon01',
    ]);

    $response->assertRedirect(route('campaigns.show', $campaign));
    expect($player->joinedCampaigns()->where('campaign_id', $campaign->id)->exists())->toBeTrue();
});

test('gm can view campaign dashboard with party characters', function () {
    $gm = User::factory()->create();
    $player = User::factory()->create();

    $system = System::create([
        'name' => 'D&D 5.5E (2024) SRD',
        'code' => 'dnd5e_2024',
    ]);

    $campaign = Campaign::create([
        'name' => 'Tomb of Annihilation',
        'description' => 'Jungle hex crawl',
        'gm_id' => $gm->id,
        'invite_code' => 'TOMB2024',
        'status' => 'active',
        'system_id' => $system->id,
    ]);

    $campaign->players()->attach($player->id);

    $character = Character::create([
        'user_id' => $player->id,
        'campaign_id' => $campaign->id,
        'system_id' => $system->id,
        'name' => 'Valen Sunstrider',
        'sheet_data' => [
            'species' => 'Elf',
            'class' => 'Paladin',
            'level' => 5,
            'hp' => ['current' => 44, 'max' => 44, 'temp' => 0],
        ],
    ]);

    $response = $this->actingAs($gm)->get(route('campaigns.show', $campaign));

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Campaigns/Show')
            ->where('isGm', true)
            ->has('campaign.characters', 1)
            ->where('campaign.characters.0.id', $character->id)
        );
});

test('gm can update campaign details and non-gm is forbidden', function () {
    $gm = User::factory()->create();
    $player = User::factory()->create();

    $system = System::create([
        'name' => 'D&D 5.5E (2024) SRD',
        'code' => 'dnd5e_2024',
    ]);

    $campaign = Campaign::create([
        'name' => 'Old Title',
        'description' => 'Old description',
        'gm_id' => $gm->id,
        'invite_code' => 'INVITE01',
        'status' => 'active',
        'system_id' => $system->id,
    ]);

    $campaign->players()->attach($player->id);

    $this->actingAs($player)
        ->patch(route('campaigns.update', $campaign), [
            'name' => 'Hacked Title',
            'status' => 'paused',
        ])
        ->assertForbidden();

    $this->actingAs($gm)
        ->patch(route('campaigns.update', $campaign), [
            'name' => 'Updated Campaign Title',
            'description' => 'Updated description',
            'status' => 'paused',
            'regenerate_invite_code' => true,
        ])
        ->assertRedirect();

    $campaign->refresh();
    expect($campaign->name)->toBe('Updated Campaign Title')
        ->and($campaign->description)->toBe('Updated description')
        ->and($campaign->status)->toBe('paused')
        ->and($campaign->invite_code)->not->toBe('INVITE01');
});

test('gm can delete campaign and characters become standalone', function () {
    $gm = User::factory()->create();
    $player = User::factory()->create();

    $system = System::create([
        'name' => 'D&D 5.5E (2024) SRD',
        'code' => 'dnd5e_2024',
    ]);

    $campaign = Campaign::create([
        'name' => 'Doomed Campaign',
        'gm_id' => $gm->id,
        'invite_code' => 'DOOMED01',
        'status' => 'active',
        'system_id' => $system->id,
    ]);

    $character = Character::create([
        'user_id' => $player->id,
        'campaign_id' => $campaign->id,
        'system_id' => $system->id,
        'name' => 'Survivor Hero',
        'sheet_data' => ['level' => 3],
    ]);

    $this->actingAs($player)
        ->delete(route('campaigns.destroy', $campaign))
        ->assertForbidden();

    $this->actingAs($gm)
        ->delete(route('campaigns.destroy', $campaign))
        ->assertRedirect(route('campaigns.index'));

    expect(Campaign::find($campaign->id))->toBeNull();
    expect($character->fresh()?->campaign_id)->toBeNull();
});
