<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Character;
use App\Models\System;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CampaignController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        System::firstOrCreate(
            ['code' => 'dnd5e_2024'],
            ['name' => 'D&D 5.5E (2024) SRD'],
        );

        $systems = System::orderBy('name')->get(['id', 'name', 'code']);

        $gmCampaigns = $user->gmCampaigns()
            ->with(['system:id,name,code', 'gm:id,username,avatar_url'])
            ->withCount(['players', 'characters'])
            ->latest()
            ->get();

        $playerCampaigns = $user->joinedCampaigns()
            ->with(['system:id,name,code', 'gm:id,username,avatar_url'])
            ->withCount(['players', 'characters'])
            ->latest('campaign_user.created_at')
            ->get();

        return Inertia::render('Campaigns/Index', [
            'systems' => $systems,
            'gmCampaigns' => $gmCampaigns,
            'playerCampaigns' => $playerCampaigns,
        ]);
    }

    public function show(Request $request, Campaign $campaign): Response
    {
        $user = $request->user();

        $isGm = $campaign->gm_id === $user->id;
        $isPlayer = $campaign->players()->where('users.id', $user->id)->exists();

        abort_unless($isGm || $isPlayer, 403);

        $campaign->load([
            'system:id,name,code',
            'gm:id,username,avatar_url',
            'players:id,username,avatar_url',
            'characters' => fn ($query) => $query
                ->with(['user:id,username,avatar_url', 'system:id,name,code'])
                ->orderBy('name'),
        ]);

        $systems = System::orderBy('name')->get(['id', 'name', 'code']);

        $assignableCharacters = $user->characters()
            ->where(function ($query) use ($campaign) {
                $query->whereNull('campaign_id')
                    ->orWhere('campaign_id', '!=', $campaign->id);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'campaign_id', 'sheet_data']);

        return Inertia::render('Campaigns/Show', [
            'campaign' => $campaign,
            'isGm' => $isGm,
            'systems' => $systems,
            'assignableCharacters' => $assignableCharacters,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'system_id' => ['nullable', 'uuid', 'exists:systems,id'],
        ]);

        $systemId = $validated['system_id'] ?? System::firstOrCreate(
            ['code' => 'dnd5e_2024'],
            ['name' => 'D&D 5.5E (2024) SRD'],
        )->id;

        do {
            $inviteCode = Str::upper(Str::random(8));
        } while (Campaign::where('invite_code', $inviteCode)->exists());

        $campaign = Campaign::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'gm_id' => $request->user()->id,
            'invite_code' => $inviteCode,
            'status' => 'active',
            'system_id' => $systemId,
        ]);

        return redirect()->route('campaigns.show', $campaign);
    }

    public function join(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'invite_code' => ['required', 'string', 'max:32'],
        ]);

        $code = Str::upper(trim($validated['invite_code']));
        $campaign = Campaign::where('invite_code', $code)->first();

        if (! $campaign) {
            throw ValidationException::withMessages([
                'invite_code' => 'No campaign found with that invite code.',
            ]);
        }

        $user = $request->user();

        if ($campaign->gm_id === $user->id) {
            throw ValidationException::withMessages([
                'invite_code' => 'You are already the DM of this campaign.',
            ]);
        }

        if ($user->joinedCampaigns()->where('campaign_id', $campaign->id)->exists()) {
            throw ValidationException::withMessages([
                'invite_code' => 'You have already joined this campaign.',
            ]);
        }

        $campaign->players()->attach($user->id);

        return redirect()->route('campaigns.show', $campaign);
    }

    public function assignCharacter(Request $request, Campaign $campaign): RedirectResponse
    {
        $user = $request->user();

        $isGm = $campaign->gm_id === $user->id;
        $isPlayer = $campaign->players()->where('users.id', $user->id)->exists();

        abort_unless($isGm || $isPlayer, 403);

        $validated = $request->validate([
            'character_id' => ['required', 'uuid', 'exists:characters,id'],
        ]);

        $character = Character::where('id', $validated['character_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        $character->update([
            'campaign_id' => $campaign->id,
        ]);

        return back();
    }

    public function update(Request $request, Campaign $campaign): RedirectResponse
    {
        abort_unless($campaign->gm_id === $request->user()->id, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'string', 'in:active,paused,completed,archived'],
            'system_id' => ['sometimes', 'required', 'uuid', 'exists:systems,id'],
            'regenerate_invite_code' => ['nullable', 'boolean'],
        ]);

        $attributes = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
        ];

        if (isset($validated['system_id'])) {
            $attributes['system_id'] = $validated['system_id'];
        }

        if (! empty($validated['regenerate_invite_code'])) {
            do {
                $inviteCode = Str::upper(Str::random(8));
            } while (Campaign::where('invite_code', $inviteCode)->where('id', '!=', $campaign->id)->exists());

            $attributes['invite_code'] = $inviteCode;
        }

        $campaign->update($attributes);

        return back();
    }

    public function destroy(Request $request, Campaign $campaign): RedirectResponse
    {
        abort_unless($campaign->gm_id === $request->user()->id, 403);

        $campaign->delete();

        return redirect()->route('campaigns.index');
    }
}
