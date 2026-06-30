<script setup lang="ts">
import { ref } from 'vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, UserRoundCog, Gavel, FileUp, FileText, BarChart3 } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const user = usePage().props.auth.user;

const mainNavItems: NavItem[] = [
    { title: 'Dashboard', href: route('dashboard'), icon: LayoutGrid },
    { title: 'Jurisprudence', href: route('jurisprudence.index'), icon: Gavel },
    { title: 'Republic Acts', href: route('republic.index'), icon: Gavel },
    {
        title: 'Executive Issuances',
        href: '#',
        icon: FileText,
        children: [
            { title: 'Presidential Decrees', href: route('presidential.index') }, 
            { title: 'Executive Orders', href: route('execord.index') },
            { title: 'Administrative Orders', href: route('ao.index') },
            { title: 'Memorandum Orders', href: route('mo.index') },
            { title: 'Memorandum Circulars', href: route('mc.index') },
            { title: 'Proclamations', href: route('proclamation.index') },
            { title: 'General Orders', href: route('genor.index') },
            { title: 'Special Orders', href: route('dashboard') },
        ],
    },
    { title: 'User Management', href: route('users.index'), icon: UserRoundCog },
];

const adminNavItems: NavItem[] = [
    { title: 'Import Cases', href: route('jurisprudence.index'), icon: FileUp },
];

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

            <div v-if="user.role === 'admin'">
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