<?php

namespace Database\Seeders\Permissions;

use App\Enums\ROLE as ROLE_ENUM;
use App\Models\Role;
use App\Services\ACLService;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    private ACLService $aclService;

    public function __construct(ACLService $aclService)
    {
        $this->aclService = $aclService;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Define roles
        $userRole = $this->aclService->createRole(ROLE_ENUM::USER);
        $adminRole = $this->aclService->createRole(ROLE_ENUM::ADMIN);

        // Create scoped permissions
        $this->aclService->createScopePermissions('users', ['create', 'read', 'update', 'delete']);
        $this->aclService->createScopePermissions('events', ['create', 'read', 'update', 'delete']);
        $this->aclService->createScopePermissions('event_attendees', ['create', 'read', 'update', 'delete']);
        $this->aclService->createScopePermissions('notifications', ['create', 'read', 'update', 'delete']);
        // Assign permissions to roles
        $this->aclService->assignScopePermissionsToRole($adminRole, 'users', ['create', 'read', 'update', 'delete']);
        $this->aclService->assignScopePermissionsToRole($adminRole, 'events', ['create', 'read', 'update', 'delete']);
        $this->aclService->assignScopePermissionsToRole($adminRole, 'event_attendees', ['create', 'read', 'update', 'delete']);
        $this->aclService->assignScopePermissionsToRole($adminRole, 'notifications', ['create', 'read', 'update', 'delete']);


        $this->aclService->assignScopePermissionsToRole($userRole, 'events', ['create', 'read','update','delete']);
        $this->aclService->assignScopePermissionsToRole($userRole, 'event_attendees', ['create','read','delete']);
        $this->aclService->assignScopePermissionsToRole($userRole, 'notifications', ['create','read','update']);
    }

    public function rollback()
    {
        $adminRole = Role::where('name', ROLE_ENUM::ADMIN)->first();
        $this->aclService->removeScopePermissionsFromRole($adminRole, 'users', ['create', 'read', 'update', 'delete']);
    }
}
