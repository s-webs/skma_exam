import { type NavItem } from '@/types';
import {
    ClipboardList,
    FileText,
    KeyRound,
    LayoutGrid,
    Shield,
    UserCheck,
    Users,
    type LucideIcon,
} from 'lucide-react';

export type AdminNavMode = 'hide' | 'lock';

export type CanPermission = (permission: string) => boolean;

interface AdminNavDefinition {
    titleKey: string;
    url: string;
    icon: LucideIcon;
    permission: string | null;
}

export const ADMIN_NAV_DEFINITIONS: AdminNavDefinition[] = [
    {
        titleKey: 'sidebar.dashboard',
        url: '/admin/dashboard',
        icon: LayoutGrid,
        permission: null,
    },
    {
        titleKey: 'sidebar.examTypes',
        url: '/admin/exam-types',
        icon: FileText,
        permission: 'exam-types.view',
    },
    {
        titleKey: 'sidebar.exams',
        url: '/admin/exams',
        icon: ClipboardList,
        permission: 'exams.view',
    },
    {
        titleKey: 'sidebar.applicants',
        url: '/admin/applicants',
        icon: UserCheck,
        permission: 'applicants.view',
    },
    {
        titleKey: 'sidebar.users',
        url: '/admin/users',
        icon: Users,
        permission: 'users.view',
    },
    {
        titleKey: 'sidebar.roles',
        url: '/admin/roles',
        icon: Shield,
        permission: 'roles.view',
    },
    {
        titleKey: 'sidebar.permissions',
        url: '/admin/permissions',
        icon: KeyRound,
        permission: 'permissions.view',
    },
];

export function hasNavPermission(can: CanPermission, permission: string | null): boolean {
    if (permission === null) {
        return true;
    }

    return can(permission);
}

export function filterNavByPermission(
    items: NavItem[],
    can: CanPermission,
    mode: AdminNavMode = 'hide',
): NavItem[] {
    return items.flatMap((item) => {
        const allowed = hasNavPermission(can, item.permission ?? null);

        if (allowed) {
            return [{ ...item, locked: false }];
        }

        if (mode === 'lock') {
            return [{ ...item, locked: true }];
        }

        return [];
    });
}

export function buildAdminNavItems(
    t: (key: string) => string,
    can: CanPermission,
    mode: AdminNavMode = 'hide',
): NavItem[] {
    const items: NavItem[] = ADMIN_NAV_DEFINITIONS.map((definition) => ({
        title: t(definition.titleKey),
        url: definition.url,
        icon: definition.icon,
        permission: definition.permission,
    }));

    return filterNavByPermission(items, can, mode);
}
