<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $name
 * @property string|null $description
 * @property string $gm_id
 * @property string $invite_code
 * @property string $status
 * @property string $system_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'description', 'gm_id', 'invite_code', 'status', 'system_id'])]
class Campaign extends Model
{
    use HasUuids;

    public function system(): BelongsTo
    {
        return $this->belongsTo(System::class);
    }

    public function gm(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gm_id');
    }

    public function players(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'campaign_user')
            ->withTimestamps();
    }

    public function characters(): HasMany
    {
        return $this->hasMany(Character::class);
    }
}
