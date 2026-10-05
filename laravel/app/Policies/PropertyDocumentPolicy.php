<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Caretaker;
use App\Models\Owner;
use App\Models\PropertyDocument;
use App\Models\Renter;

/**
 * Who may do what with a rules document. DENY BY DEFAULT.
 *
 * These are the landlord's terms, so a renter may READ them but never change
 * them. A document with a null property_id applies portfolio-wide.
 */
class PropertyDocumentPolicy
{
    public function viewAny(Owner|Caretaker|Renter $actor): bool
    {
        return true;
    }

    public function view(Owner|Caretaker|Renter $actor, PropertyDocument $document): bool
    {
        if ($actor instanceof Owner) {
            return true;
        }

        if ($actor instanceof Caretaker) {
            $ids = $actor->assignedPropertyIds();

            return $document->property_id === null || in_array((int) $document->property_id, $ids, true);
        }

        // A renter sees active documents for their own property, and the
        // portfolio-wide ones.
        if (! $document->is_active) {
            return false;
        }

        return $document->property_id === null
            || (int) $document->property_id === (int) $actor->property_id;
    }

    /** Only the owner authors the terms. */
    public function create(Owner|Caretaker|Renter $actor): bool
    {
        return $actor instanceof Owner;
    }

    public function update(Owner|Caretaker|Renter $actor, PropertyDocument $document): bool
    {
        return $actor instanceof Owner;
    }

    public function delete(Owner|Caretaker|Renter $actor, PropertyDocument $document): bool
    {
        return $actor instanceof Owner;
    }
}