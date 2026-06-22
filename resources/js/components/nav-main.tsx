import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { Lock } from 'lucide-react';
import { useTranslation } from 'react-i18next';

export function NavMain({ items = [] }: { items: NavItem[] }) {
    const page = usePage();
    const { t } = useTranslation();

    return (
        <SidebarGroup className="px-2 py-0">
            <SidebarGroupLabel>Platform</SidebarGroupLabel>
            <SidebarMenu>
                {items.map((item) => (
                    <SidebarMenuItem key={item.title}>
                        {item.locked ? (
                            <Tooltip>
                                <TooltipTrigger asChild>
                                    <SidebarMenuButton
                                        disabled
                                        className="cursor-not-allowed opacity-60"
                                        aria-disabled
                                    >
                                        {item.icon && <item.icon />}
                                        <span>{item.title}</span>
                                        <Lock className="ml-auto h-4 w-4" />
                                    </SidebarMenuButton>
                                </TooltipTrigger>
                                <TooltipContent side="right">
                                    {t('sidebar.noAccess')}
                                </TooltipContent>
                            </Tooltip>
                        ) : (
                            <SidebarMenuButton asChild isActive={item.url === page.url}>
                                <Link href={item.url} prefetch>
                                    {item.icon && <item.icon />}
                                    <span>{item.title}</span>
                                </Link>
                            </SidebarMenuButton>
                        )}
                    </SidebarMenuItem>
                ))}
            </SidebarMenu>
        </SidebarGroup>
    );
}
