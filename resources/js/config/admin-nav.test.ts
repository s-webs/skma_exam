import { describe, expect, it } from 'vitest';
import { buildAdminNavItems, filterNavByPermission } from '@/config/admin-nav';
import { type NavItem } from '@/types';

const t = (key: string) => key;

const sampleItems: NavItem[] = [
    { title: 'Dashboard', url: '/admin/dashboard', permission: null },
    { title: 'Exam Types', url: '/admin/exam-types', permission: 'exam-types.view' },
    { title: 'Users', url: '/admin/users', permission: 'users.view' },
];

describe('filterNavByPermission', () => {
    it('shows only dashboard for user without permissions in hide mode', () => {
        const can = () => false;

        const visible = filterNavByPermission(sampleItems, can, 'hide');

        expect(visible).toHaveLength(1);
        expect(visible[0]?.url).toBe('/admin/dashboard');
    });

    it('shows dashboard and exam types for registrator-like permissions in hide mode', () => {
        const can = (permission: string) => permission === 'exam-types.view';

        const visible = filterNavByPermission(sampleItems, can, 'hide');

        expect(visible.map((item) => item.url)).toEqual([
            '/admin/dashboard',
            '/admin/exam-types',
        ]);
    });

    it('marks inaccessible items as locked in lock mode', () => {
        const can = (permission: string) => permission === 'exam-types.view';

        const visible = filterNavByPermission(sampleItems, can, 'lock');

        expect(visible).toHaveLength(3);
        expect(visible.find((item) => item.url === '/admin/users')?.locked).toBe(true);
        expect(visible.find((item) => item.url === '/admin/exam-types')?.locked).toBe(false);
    });
});

describe('buildAdminNavItems', () => {
    it('shows all items for developer-like can()', () => {
        const can = () => true;

        const items = buildAdminNavItems(t, can, 'hide');

        expect(items.length).toBeGreaterThanOrEqual(7);
    });

    it('shows only dashboard for user without permissions', () => {
        const can = () => false;

        const items = buildAdminNavItems(t, can, 'hide');

        expect(items).toHaveLength(1);
        expect(items[0]?.url).toBe('/admin/dashboard');
    });
});
