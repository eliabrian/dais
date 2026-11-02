<?php

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function (User $user, string $id): bool {
    return (string) $user->id === (string) $id;
});

Broadcast::channel('campaign.{campaignId}', function (User $user, string $campaignId): bool {
    return Campaign::query()
        ->where('id', $campaignId)
        ->where(function ($query) use ($user) {
            $query->where('gm_id', $user->id)
                ->orWhereHas('players', fn ($q) => $q->where('users.id', $user->id));
        })
        ->exists();
});
