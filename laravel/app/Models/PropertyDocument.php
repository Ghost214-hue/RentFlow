<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DocumentType;
use App\Models\Concerns\BelongsToOwner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A rules / regulations document published to renters.
 *
 * Maps the EXISTING `property_documents` table. The legacy UI labels this
 * "Rules" and shows the ACTIVE documents for the current property.
 *
 * @property int $id
 * @property string $title
 * @property string $content
 * @property string $type    rules|regulations|policy|other
 * @property string $version
 * @property bool $is_active
 */
class PropertyDocument extends Model
{
    /** @use HasFactory<\Database\Factories\PropertyDocumentFactory> */
    use BelongsToOwner;
    use HasFactory;

    protected $table = 'property_documents';

    /** @return array<int, string> */
    protected $fillable = [
        'property_id',
        'title',
        'content',
        'type',
        'version',
        'is_active',
        'published_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'type' => DocumentType::class,
            'is_active' => 'boolean',
            'published_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}