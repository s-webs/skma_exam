import { usePage } from '@inertiajs/react';

interface SharedAuth {
    auth: {
        permissions?: string[];
        isDeveloper?: boolean;
    };
}

export function usePermissions() {
    const { auth } = usePage<SharedAuth>().props;
    const permissions = auth.permissions ?? [];

    const can = (permission: string): boolean => {
        if (auth.isDeveloper) {
            return true;
        }

        return permissions.includes(permission);
    };

    const canAny = (required: string[]): boolean => {
        if (auth.isDeveloper) {
            return true;
        }

        return required.some((permission) => permissions.includes(permission));
    };

    const canAll = (required: string[]): boolean => {
        if (auth.isDeveloper) {
            return true;
        }

        return required.every((permission) => permissions.includes(permission));
    };

    return {
        permissions,
        isDeveloper: auth.isDeveloper ?? false,
        can,
        canAny,
        canAll,
    };
}
