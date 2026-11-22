<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Admin;

class AdminPolicy
{
    /** Auditor, support, super_admin can all view. */
    public function viewAny(Admin $admin): bool
    {
        return in_array($admin->admin_role, ['super_admin', 'support', 'auditor'], true);
    }

    public function view(Admin $admin): bool
    {
        return in_array($admin->admin_role, ['super_admin', 'support', 'auditor'], true);
    }

    /** Support and super_admin can create/revoke support access grants. */
    public function manageGrants(Admin $admin): bool
    {
        return in_array($admin->admin_role, ['super_admin', 'support'], true);
    }

    /** Only super_admin can suspend, activate, reset, or manage owners/admins. */
    public function manageOwners(Admin $admin): bool
    {
        return $admin->admin_role === 'super_admin';
    }

    public function manageAdmins(Admin $admin): bool
    {
        return $admin->admin_role === 'super_admin';
    }
}
