import { LayoutDashboard, LayoutGrid } from '@lucide/vue';
import { dashboard } from '@/routes';
import { edit as editAddress } from '@/routes/address';
import { dashboard as adminDashboard } from '@/routes/admin';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import type { NavItem, NavigationContext } from '@/types';

export type NavItemFactory = () => NavItem;

const userNavItemFactories: NavItemFactory[] = [
    () => ({
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
        visibility: 'shared',
    }),
];

const adminNavItemFactories: NavItemFactory[] = [
    () => ({
        title: 'Admin Dashboard',
        href: adminDashboard(),
        icon: LayoutDashboard,
        visibility: 'admin',
    }),
];

export const settingsNavItems: NavItem[] = [
    {
        title: 'Profile',
        href: editProfile(),
    },
    {
        title: 'Address',
        href: editAddress(),
    },
    {
        title: 'Security',
        href: editSecurity(),
    },
    {
        title: 'Appearance',
        href: editAppearance(),
    },
];

export function getMainNavItems(
    context: NavigationContext,
    customItems: NavItem[] = [],
): NavItem[] {
    const factories = customItems.length
        ? customItems.map((item) => () => item)
        : context === 'admin'
          ? adminNavItemFactories
          : userNavItemFactories;

    return factories
        .map((factory) => factory())
        .filter(
            (item) =>
                item.visibility === undefined ||
                item.visibility === 'shared' ||
                item.visibility === context,
        );
}

export function getNavigationHomeHref(
    context: NavigationContext,
): NavItem['href'] {
    return context === 'admin' ? adminDashboard() : dashboard();
}
