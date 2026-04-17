<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { 
  Gavel, 
  FileText, 
  Users, 
  ArrowUpRight, 
  Database,
  ShieldCheck,
  FileBadge
} from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
];

// Mock data structure including categorizations
const stats = {
    jurisprudenceCount: 45280,
    issuancesCount: 12450,
    userCount: 15,
    recentCases: [
        { id: 1, gr_number: 'G.R. No. 252329', title: 'People vs. Genosa', date: '2024-03-15' },
        { id: 2, gr_number: 'G.R. No. 123456', title: 'Smith vs. Republic', date: '2024-03-12' },
    ],
    recentIssuances: [
        { id: 1, type: 'Executive Order', number: 'E.O. No. 52', title: 'Reorganizing the Administrative Board', date: '2024-03-14' },
        { id: 2, type: 'Administrative Order', number: 'A.O. No. 12', title: 'Revised Guidelines for Legal Aid', date: '2024-03-10' },
        { id: 3, type: 'Proclamation', number: 'Proc. No. 1082', title: 'Declaring a State of Emergency', date: '2024-03-08' },
    ]
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <Card class="border-sidebar-border/70 shadow-sm">
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Total Jurisprudence</CardTitle>
                        <Gavel class="h-4 w-4 text-primary" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.jurisprudenceCount.toLocaleString() }}</div>
                        <p class="text-xs text-muted-foreground mt-1 font-medium">Active Decisions</p>
                    </CardContent>
                </Card>

                <Card class="border-sidebar-border/70 shadow-sm">
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Executive Issuances</CardTitle>
                        <FileBadge class="h-4 w-4 text-amber-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.issuancesCount.toLocaleString() }}</div>
                        <p class="text-xs text-muted-foreground mt-1 font-medium">All Categories</p>
                    </CardContent>
                </Card>

                <Card class="border-sidebar-border/70 shadow-sm">
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">System Integrity</CardTitle>
                        <ShieldCheck class="h-4 w-4 text-emerald-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">100%</div>
                        <p class="text-xs text-muted-foreground mt-1 font-medium">Data Sync Status</p>
                    </CardContent>
                </Card>
            </div>

            <div class="flex-1 rounded-xl border border-sidebar-border/70 bg-card">
                <Tabs defaultValue="jurisprudence" class="flex flex-col h-full">
                    <div class="flex items-center justify-between px-6 py-4 border-b">
                        <TabsList class="grid w-[400px] grid-cols-2">
                            <TabsTrigger value="jurisprudence">Jurisprudence</TabsTrigger>
                            <TabsTrigger value="issuances">Executive Issuances</TabsTrigger>
                        </TabsList>
                        <Button variant="ghost" size="sm" class="text-xs">View Full Databank</Button>
                    </div>

                    <TabsContent value="jurisprudence" class="m-0 flex-1 overflow-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-muted/30 sticky top-0 border-b">
                                <tr class="text-left text-muted-foreground">
                                    <th class="p-4 font-medium">G.R. Number</th>
                                    <th class="p-4 font-medium">Title</th>
                                    <th class="p-4 font-medium">Uploaded</th>
                                    <th class="p-4 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="item in stats.recentCases" :key="item.id" class="hover:bg-muted/10 transition-colors">
                                    <td class="p-4 font-mono text-xs font-semibold">{{ item.gr_number }}</td>
                                    <td class="p-4">{{ item.title }}</td>
                                    <td class="p-4 text-muted-foreground">{{ item.date }}</td>
                                    <td class="p-4 text-right">
                                        <Button variant="ghost" size="icon" class="h-8 w-8"><ArrowUpRight class="h-4 w-4" /></Button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </TabsContent>

                    <TabsContent value="issuances" class="m-0 flex-1 overflow-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-muted/30 sticky top-0 border-b">
                                <tr class="text-left text-muted-foreground">
                                    <th class="p-4 font-medium">Reference No.</th>
                                    <th class="p-4 font-medium">Category</th>
                                    <th class="p-4 font-medium">Subject</th>
                                    <th class="p-4 font-medium">Date</th>
                                    <th class="p-4 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="item in stats.recentIssuances" :key="item.id" class="hover:bg-muted/10 transition-colors">
                                    <td class="p-4 font-semibold text-xs">{{ item.number }}</td>
                                    <td class="p-4">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-primary/10 text-primary border border-primary/20">
                                            {{ item.type }}
                                        </span>
                                    </td>
                                    <td class="p-4 truncate max-w-[300px]">{{ item.title }}</td>
                                    <td class="p-4 text-muted-foreground">{{ item.date }}</td>
                                    <td class="p-4 text-right">
                                        <Button variant="ghost" size="icon" class="h-8 w-8"><ArrowUpRight class="h-4 w-4" /></Button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </TabsContent>

                    <div class="p-4 border-t bg-muted/10 flex items-center justify-between text-[10px] text-muted-foreground uppercase tracking-widest">
                        <div class="flex items-center gap-2">
                            <Database class="h-3 w-3" />
                            System Sync: <span class="text-emerald-600 font-bold">Stable</span>
                        </div>
                        <div class="font-medium">
                            Arellano Law Foundation Databank
                        </div>
                    </div>
                </Tabs>
            </div>
        </div>
    </AppLayout>
</template>