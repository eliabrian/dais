<?php

namespace App\Http\Controllers;

use App\Events\CharacterUpdated;
use App\Models\Campaign;
use App\Models\Character;
use App\Models\CompendiumEntry;
use App\Models\System;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CharacterController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        System::firstOrCreate(
            ['code' => 'dnd5e_2024'],
            ['name' => 'D&D 5.5E (2024) SRD'],
        );

        $systems = System::orderBy('name')->get(['id', 'name', 'code']);

        $characters = $user->characters()
            ->with(['system:id,name,code', 'campaign:id,name'])
            ->latest()
            ->get();

        return Inertia::render('Characters/Index', [
            'characters' => $characters,
            'systems' => $systems,
            'availableCampaigns' => $this->availableCampaignsFor($user),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'avatar_url' => ['nullable', 'string', 'url', 'max:2048'],
            'system_id' => ['nullable', 'uuid', 'exists:systems,id'],
            'campaign_id' => ['nullable', 'uuid', 'exists:campaigns,id'],
            'species' => ['nullable', 'string', 'max:100'],
            'class' => ['nullable', 'string', 'max:100'],
            'background' => ['nullable', 'string', 'max:100'],
            'level' => ['nullable', 'integer', 'min:1', 'max:20'],
            'sheet_data' => ['nullable', 'array'],
        ]);

        if (! empty($validated['campaign_id']) && ! $this->userBelongsToCampaign($user, $validated['campaign_id'])) {
            throw ValidationException::withMessages([
                'campaign_id' => 'You can only assign a character to a campaign you have joined or run.',
            ]);
        }

        $system = ! empty($validated['system_id'])
            ? System::findOrFail($validated['system_id'])
            : System::firstOrCreate(
                ['code' => 'dnd5e_2024'],
                ['name' => 'D&D 5.5E (2024) SRD'],
            );

        $level = (int) ($validated['level'] ?? ($validated['sheet_data']['level'] ?? 1));

        $defaultSheet = $this->defaultDnd2024SheetData(
            species: $validated['species'] ?? ($validated['sheet_data']['species'] ?? 'Human'),
            className: $validated['class'] ?? ($validated['sheet_data']['class'] ?? 'Fighter'),
            background: $validated['background'] ?? ($validated['sheet_data']['background'] ?? 'Soldier'),
            level: $level,
        );

        $customSheet = $validated['sheet_data'] ?? [];
        $mergedSheet = array_replace($defaultSheet, $customSheet);

        foreach (['hp', 'hit_dice', 'death_saves', 'abilities', 'coins'] as $nestedKey) {
            if (isset($customSheet[$nestedKey]) && is_array($customSheet[$nestedKey])) {
                $mergedSheet[$nestedKey] = array_replace($defaultSheet[$nestedKey], $customSheet[$nestedKey]);
            }
        }

        $character = Character::create([
            'user_id' => $user->id,
            'campaign_id' => ($validated['campaign_id'] ?? null) ?: null,
            'system_id' => $system->id,
            'name' => $validated['name'],
            'avatar_url' => ($validated['avatar_url'] ?? null) ?: null,
            'sheet_data' => $mergedSheet,
        ]);

        return redirect()->route('characters.show', $character);
    }

    public function show(Request $request, Character $character): Response
    {
        $user = $request->user();
        $character->load(['system:id,name,code', 'campaign:id,name,gm_id', 'user:id,username']);

        $isOwner = $character->user_id === $user->id;
        $isInCampaign = $character->campaign_id && $this->userBelongsToCampaign($user, $character->campaign_id);

        abort_unless($isOwner || $isInCampaign, 403);

        $canEdit = $isOwner || ($character->campaign && $character->campaign->gm_id === $user->id);

        $compendiumEntries = CompendiumEntry::where('system_id', $character->system_id)
            ->select('id', 'type', 'name', 'summary', 'description', 'data', 'is_srd')
            ->orderBy('name')
            ->get();

        return Inertia::render('Characters/Show', [
            'character' => $character,
            'canEdit' => $canEdit,
            'availableCampaigns' => $this->availableCampaignsFor($user),
            'compendiumEntries' => $compendiumEntries,
        ]);
    }

    public function update(Request $request, Character $character): RedirectResponse
    {
        $user = $request->user();
        $character->load('campaign:id,gm_id');

        $canEdit = $character->user_id === $user->id
            || ($character->campaign && $character->campaign->gm_id === $user->id);

        abort_unless($canEdit, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'avatar_url' => ['nullable', 'string', 'url', 'max:2048'],
            'campaign_id' => ['nullable', 'uuid', 'exists:campaigns,id'],
            'sheet_data' => ['required', 'array'],
        ]);

        if (! empty($validated['campaign_id']) && ! $this->userBelongsToCampaign($user, $validated['campaign_id'])) {
            throw ValidationException::withMessages([
                'campaign_id' => 'You can only assign a character to a campaign you have joined or run.',
            ]);
        }

        $character->update([
            'name' => $validated['name'],
            'avatar_url' => ($validated['avatar_url'] ?? null) ?: null,
            'campaign_id' => ($validated['campaign_id'] ?? null) ?: null,
            'sheet_data' => $validated['sheet_data'],
        ]);

        if ($character->campaign_id) {
            rescue(fn () => broadcast(new CharacterUpdated($character))->toOthers(), report: false);
        }

        return back();
    }

    public function destroy(Request $request, Character $character): RedirectResponse
    {
        abort_unless($character->user_id === $request->user()->id, 403);

        $character->delete();

        return redirect()->route('characters.index');
    }

    /**
     * @return Collection<int, Campaign>
     */
    private function availableCampaignsFor(User $user): Collection
    {
        return Campaign::query()
            ->where('gm_id', $user->id)
            ->orWhereHas('players', fn ($query) => $query->where('users.id', $user->id))
            ->orderBy('name')
            ->get(['id', 'name', 'system_id']);
    }

    private function userBelongsToCampaign(User $user, string $campaignId): bool
    {
        return Campaign::query()
            ->where('id', $campaignId)
            ->where(function ($query) use ($user) {
                $query->where('gm_id', $user->id)
                    ->orWhereHas('players', fn ($q) => $q->where('users.id', $user->id));
            })
            ->exists();
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultDnd2024SheetData(
        string $species,
        string $className,
        string $background,
        int $level,
    ): array {
        return [
            'species' => $species,
            'class' => $className,
            'subclass' => '',
            'background' => $background,
            'origin_feat' => 'Alert',
            'level' => $level,
            'xp' => 0,
            'armor_class' => 10,
            'speed' => 30,
            'size' => 'Medium',
            'heroic_inspiration' => false,
            'exhaustion' => 0,
            'hp' => [
                'current' => 10,
                'max' => 10,
                'temp' => 0,
            ],
            'hit_dice' => [
                'total' => $level.'d10',
                'remaining' => $level,
            ],
            'death_saves' => [
                'successes' => 0,
                'failures' => 0,
            ],
            'abilities' => [
                'str' => 10,
                'dex' => 10,
                'con' => 10,
                'int' => 10,
                'wis' => 10,
                'cha' => 10,
            ],
            'saving_throws' => [],
            'skills' => [],
            'weapon_masteries' => [],
            'weapons_list' => [],
            'armors_list' => [],
            'spells_list' => [],
            'inventory_list' => [],
            'features_list' => [],
            'traits_list' => [],
            'feats_list' => [],
            'languages_list' => [],
            'weapons_attacks' => '',
            'armor_equipped' => '',
            'features_traits' => '',
            'proficiencies' => '',
            'equipment' => '',
            'coins' => [
                'cp' => 0,
                'sp' => 0,
                'ep' => 0,
                'gp' => 10,
                'pp' => 0,
            ],
            'spellcasting_ability' => 'none',
            'spells' => '',
            'notes' => '',
        ];
    }
}
