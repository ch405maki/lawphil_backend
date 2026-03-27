<script setup lang="ts">
import { ref } from 'vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { SidebarGroup, SidebarGroupLabel } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, UserRoundCog, BarChart3, Gavel, FileUp } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const user = usePage().props.auth.user;

const mainNavItems: NavItem[] = [
    { title: 'Dashboard', href: route('dashboard'), icon: LayoutGrid },
    { title: 'Jurisprudence', href: route('jurisprudence.index'), icon: Gavel },
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
            { title: 'User Logs', href: '#' },
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
            <NavMain :items="mainNavItems" />
            
            <SidebarGroup v-if="user.role === 'admin'">
                <SidebarGroupLabel>Data Management</SidebarGroupLabel>
                <NavMain :items="adminNavItems" />
            </SidebarGroup>

            <!-- <NavMain :items="reportItems" group-label="Reports" /> -->
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" v-if="user.role === 'admin'"/>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>