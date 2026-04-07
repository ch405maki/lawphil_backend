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
    {
        title: 'Executive Issuances',
        href: '#',
        icon: FileText,
        children: [
            { title: 'Jurisprudence', href: route('jurisprudence.index') },
            { title: 'Presidential Decrees', href: route('dashboard') },
            { title: 'Executive Orders', href: route('dashboard') },
            { title: 'Administrative Orders', href: route('dashboard') },
            { title: 'Memorandum Orders', href: route('dashboard') },
            { title: 'Memorandum Circulars', href: route('dashboard') },
            { title: 'Proclamations', href: route('dashboard') },
            { title: 'General Orders', href: route('dashboard') },
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
            { title: 'Case Stats', href: route('dashboard') },
            { title: 'User Logs', href: route('dashboard') },
        ],
    },
]);

const footerNavItems: NavItem[] = [
    { title: 'Configuration', href: route('profile-pictures.index'), icon: LayoutGrid },
];
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
            <NavFooter :items="footerNavItems" v-if="user.role === 'admin'"/>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>