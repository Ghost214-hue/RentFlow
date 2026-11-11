<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToOwner;
use Database\Factories\EmailTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * An owner's OWN customisation of one shipped mail template.
 *
 * Maps the EXISTING `email_templates` table. Rows are per-owner: this is a
 * tenant's saved copy, not a shared catalogue.
 *
 * The shipped bodies live in Blade, not here. This table holds only what an
 * owner overrode, which is why a portfolio with zero rows still sends correct
 * mail -- the legacy behaviour, where only owner 1 had templates and every
 * other owner silently sent nothing, is exactly what this avoids.
 *
 * @property int $id
 * @property string $name matches a MailTemplate::value
 * @property string $type
 * @property string $subject
 * @property string $body
 */
class EmailTemplate extends Model
{
    /** @use HasFactory<EmailTemplateFactory> */
    use BelongsToOwner;

    use HasFactory;

    protected $table = 'email_templates';

    protected $fillable = [
        'name',
        'type',
        'subject',
        'body',
    ];

    public function scopeForName($query, string $name)
    {
        return $query->where('name', $name);
    }
}
