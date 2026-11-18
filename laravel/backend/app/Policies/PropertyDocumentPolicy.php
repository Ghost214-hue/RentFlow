<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Caretaker;
use App\Models\Owner;
use App\Models\PropertyDocument;
use App\Models\Renter;
use App\Services\PermissionService;

class PropertyDocumentPolicy
{
    public function __construct(private readonly PermissionService $permissions) {}

    public function viewAny(Owner|Caretaker|Renter $actor): bool
    {
        return $this->permissions->allows($actor, 'documents.view');
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

        if (! $document->is_active) {
            return false;
        }

        return $document->property_id === null
            || (int) $document->property_id === (int) $actor->property_id;
    }

    public function create(Owner|Caretaker|Renter $actor): bool
    {
        return $this->permissions->allows($actor, 'documents.manage');
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