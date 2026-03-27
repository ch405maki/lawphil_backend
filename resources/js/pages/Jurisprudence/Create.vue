<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ArrowUpRightIcon, TriangleAlert } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import {
  Empty,
  EmptyContent,
  EmptyDescription,
  EmptyHeader,
  EmptyMedia,
  EmptyTitle,
} from '@/components/ui/empty'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Jusrisprudence',
        href: '#',
    },
    {
        title: 'Create',
        href: '#',
    },
];
</script>

<template>
    <Head title="Create" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <!-- content mass creation -->
             <Empty>
                <EmptyHeader>
                    <EmptyMedia variant="icon">
                        <TriangleAlert />
                    </EmptyMedia>

                    <EmptyTitle>Feature Implementation in Progress</EmptyTitle>

                    <EmptyDescription>
                        This Functionality and content is currently under development.
                    </EmptyDescription>
                </EmptyHeader>

                <EmptyContent>
                    <div class="flex gap-2">
                        <Button disabled>Coming Soon</Button>
                        <Button variant="outline" disabled>
                            Under Development
                        </Button>
                    </div>
                </EmptyContent>

                <Button variant="link" as-child class="text-muted-foreground" size="sm">
                    <a href="#">
                        View Development Updates <ArrowUpRightIcon />
                    </a>
                </Button>
            </Empty>
        </div>
    </AppLayout>
</template>
