<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\CompendiumEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $system_id
 * @property string $type
 * @property string $name
 * @property string|null $summary
 * @property string|null $description
 * @property array<string, mixed>|null $data
 * @property bool $is_srd
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read System $system
 */
#[Fillable(['system_id', 'type', 'name', 'summary', 'description', 'data', 'is_srd'])]
class CompendiumEntry extends Model
{
    /** @use HasFactory<CompendiumEntryFactory> */
    use HasFactory, HasUuids;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_srd' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<System, $this>
     */
    public function system(): BelongsTo
    {
        return $this->belongsTo(System::class);
    }
}
