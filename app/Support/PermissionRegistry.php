<?php

namespace App\Support;

class PermissionRegistry
{
    /**
     * @return list<string>
     */
    public static function all(): array
    {
        $permissions = [];

        foreach (config('permissions.groups', []) as $group) {
            foreach ($group['permissions'] as $permission) {
                $permissions[] = $permission;
            }
        }

        return $permissions;
    }

    /**
     * @return array<string, array{label: string, permissions: list<string>}>
     */
    public static function grouped(): array
    {
        return config('permissions.groups', []);
    }

    /**
     * @return list<string>
     */
    public static function forRole(string $roleName): array
    {
        $defaults = config('permissions.default_roles', []);

        if (! isset($defaults[$roleName])) {
            return [];
        }

        if ($defaults[$roleName] === '*') {
            return self::all();
        }

        return $defaults[$roleName];
    }
}
