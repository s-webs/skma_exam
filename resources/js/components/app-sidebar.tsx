import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { LayoutGrid, FileText, Users, ClipboardList, UserCheck } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import AppLogo from './app-logo';

const footerNavItems: NavItem[] = [];

interface SharedAuth {
    auth: {
        roles?: string[];
        isDeveloper?: boolean;
        isRegistrator?: boolean;
        user?: {
            roles?: Array<{ name: string } | string>;
        };
    };
}

function resolveRoles(auth: SharedAuth['auth']): string[] {
    if (auth.roles?.length) {
        return auth.roles;
    }

    return (auth.user?.roles ?? []).map((role) => (typeof role === 'string' ? role : role.name));
}

export function AppSidebar() {
    const { t } = useTranslation();
    const { auth } = usePage<SharedAuth>().props;
    const roles = resolveRoles(auth);
    const isDeveloper = auth.isDeveloper ?? roles.includes('developer');
    const canManageExamsAndApplicants = isDeveloper || roles.includes('ktbo');

    const mainNavItems: NavItem[] = [
        {
            title: t('sidebar.dashboard'),
            url: '/admin/dashboard',
            icon: LayoutGrid,
        },
        {
            title: t('sidebar.examTypes'),
            url: '/admin/exam-types',
            icon: FileText,
        },
    ];

    if (canManageExamsAndApplicants) {
        mainNavItems.push(
            {
                title: t('sidebar.exams'),
                url: '/admin/exams',
                icon: ClipboardList,
            },
            {
                title: t('sidebar.applicants'),
                url: '/admin/applicants',
                icon: UserCheck,
            },
        );
    }

    if (isDeveloper) {
        mainNavItems.push({
            title: t('sidebar.users'),
            url: '/admin/users',
            icon: Users,
        });
    }

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <Link href="/admin/dashboard" prefetch>
                    <AppLogo />
                </Link>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
