<script setup lang="ts">
    import { 
        SidebarGroup, 
        SidebarGroupLabel, 
        SidebarMenu, 
        SidebarMenuButton, 
        SidebarMenuItem,
        SidebarMenuSub,
        SidebarMenuSubItem
    } from '@/components/ui/sidebar';
    import { Collapsible, CollapsibleTrigger, CollapsibleContent } from '@/components/ui/collapsible';
    import { 
        ChevronDown,
    } from 'lucide-vue-next';
    import { Link, usePage } from '@inertiajs/vue3';
    import { type NavItem } from '@/types';
    import { type SharedData } from '@/types';

    interface DropdownNavItem extends NavItem {
        children?: NavItem[];
        isOpen?: boolean;
    }

    defineProps<{
        items: DropdownNavItem[];
        groupLabel?: string;
    }>();

    const page = usePage<SharedData>();

    const toggleDropdown = (item: DropdownNavItem) => {
    if (item.children) {
        item.isOpen = !item.isOpen;
    }
    };

    const isActive = (href: string) => {
    return page.url.startsWith(href);
    };
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel v-if="groupLabel">{{ groupLabel }}</SidebarGroupLabel>
        <SidebarMenu>
        <template v-for="item in items" :key="item.title">
            <!-- Regular menu items -->
            <SidebarMenuItem v-if="!item.children">
            <SidebarMenuButton 
                as-child 
                :is-active="isActive(item.href)"
                :class="{ 'border border-sidebar-border/70': isActive(item.href) }"
                :tooltip="item.title"
                >
                <Link :href="item.href">
                <component :is="item.icon" class="w-4 h-4" />
                <span>{{ item.title }}</span>
                </Link>
            </SidebarMenuButton>
            </SidebarMenuItem>
            
            <!-- Dropdown menu items -->
            <template v-else>
            <Collapsible :open="item.isOpen" class="group/collapsible">
                <SidebarMenuItem>
                <CollapsibleTrigger :class="{ 'border border-zinc-300/50': item.isOpen }" asChild @click="toggleDropdown(item)" >
                    <SidebarMenuButton 
                        :is-active="item.children?.some(child => isActive(child.href))"
                        :tooltip="item.title"
                        >
                    <component :is="item.icon" class="w-4 h-4" />
                    <span>{{ item.title }}</span>
                    <ChevronDown 
                        class="w-4 h-4 ml-auto transition-transform duration-200" 
                        :class="{ 'rotate-180': item.isOpen }" 
                    />
                    </SidebarMenuButton>
                </CollapsibleTrigger>
                </SidebarMenuItem>
                
                <CollapsibleContent>
                <SidebarMenuSub>
                    <SidebarMenuSubItem v-for="child in item.children" :key="child.title">
                    <SidebarMenuButton 
                        as-child
                        :is-active="isActive(child.href)"
                        >
                        <Link :href="child.href">
                        <component :is="child.icon" class="w-4 h-4" />
                        <span>{{ child.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                    </SidebarMenuSubItem>
                </SidebarMenuSub>
                </CollapsibleContent>
            </Collapsible>
            </template>
        </template>
        </SidebarMenu>
    </SidebarGroup>
</template>