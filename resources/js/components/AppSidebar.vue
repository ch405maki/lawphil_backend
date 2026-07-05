<script setup lang="ts">
import { ref, computed } from 'vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, UserRoundCog, Gavel, FileUp, FileText, BarChart3, Settings2, Shield } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { usePermissions } from '@/composables/usePermissions';

const { can, isAdmin } = usePermissions();

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [];

    items.push({ title: 'Dashboard', href: route('dashboard'), icon: LayoutGrid });

    if (can('jurisprudence', 'view')) {
        items.push({ title: 'Jurisprudence', href: route('jurisprudence.index'), icon: Gavel });
    }
    if (can('republic', 'view')) {
        items.push({ title: 'Republic Acts', href: route('republic.index'), icon: Gavel });
    }

    const issuancesChildren: NavItem[] = [];
    if (can('presidential', 'view')) issuancesChildren.push({ title: 'Presidential Decrees', href: route('presidential.index') });
    if (can('execord', 'view')) issuancesChildren.push({ title: 'Executive Orders', href: route('execord.index') });
    if (can('ao', 'view')) issuancesChildren.push({ title: 'Administrative Orders', href: route('ao.index') });
    if (can('mo', 'view')) issuancesChildren.push({ title: 'Memorandum Orders', href: route('mo.index') });
    if (can('mc', 'view')) issuancesChildren.push({ title: 'Memorandum Circulars', href: route('mc.index') });
    if (can('proclamation', 'view')) issuancesChildren.push({ title: 'Proclamations', href: route('proclamation.index') });
    if (can('genor', 'view')) issuancesChildren.push({ title: 'General Orders', href: route('genor.index') });

    if (issuancesChildren.length > 0) {
        items.push({
            title: 'Executive Issuances',
            href: '#',
            icon: FileText,
            children: issuancesChildren,
        });
    }

    if (can('users', 'view')) {
        items.push({ title: 'User Management', href: route('users.index'), icon: UserRoundCog });
    }

    return items;
});

const adminNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [];

    if (can('jurisprudence', 'create')) {
        items.push({ title: 'Import Cases', href: route('jurisprudence.index'), icon: FileUp });
    }
    if (can('users', 'view')) {
        items.push({ title: 'Permissions', href: route('permissions.index'), icon: Shield });
    }

    return items;
});

const reportItems = ref([
    {
        title: 'Reports',
        href: '#',
        icon: BarChart3,
        children: [
            { title: 'Case Stats', href: '#' },
            { title: 'Activity Logs', href: '/logs' },
        ],
    },
]);

</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" group-label="Navigation"/>

            <div v-if="isAdmin">
                <NavMain :items="adminNavItems" group-label="Data Management"/>
            </div>
            
            <NavMain :items="reportItems" group-label="Reports" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>