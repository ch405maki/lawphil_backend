<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { formatDate, formatDateTime } from '@/lib/utils';

import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';

import {
  FileText,
  Calendar,
  CheckCircle,
  XCircle,
  Activity,
  Plus,
  Pencil,
  Trash2,
} from 'lucide-vue-next';

interface DashboardStats {
  total_records: number;
  records_this_month: number;
  records_with_pdf: number;
  records_without_pdf: number;
}

interface ActivityItem {
  id: number;
  description: string;
  event: string;
  log_name: string;
  causer: { id: number; name: string; email: string } | null;
  created_at: string;
}

interface JurisprudenceItem {
  id: number;
  gr_number: string;
  date: string;
  ponente: string;
  subject: string;
}

const props = defineProps<{
  stats: DashboardStats;
  recentActivities: ActivityItem[];
  recentJurisprudence: JurisprudenceItem[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
];

const eventConfig: Record<string, { icon: any; badge: string; container: string }> = {
  created: {
    icon: Plus,
    badge: 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-300',
    container: 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400',
  },
  updated: {
    icon: Pencil,
    badge: 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300',
    container: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400',
  },
  deleted: {
    icon: Trash2,
    badge: 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300',
    container: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400',
  },
};

const fallbackEvent = {
  icon: Activity,
  badge: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
  container: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
};

function getEventConfig(event: string) {
  return eventConfig[event] || fallbackEvent;
}

const monthName = new Date().toLocaleString('default', { month: 'long' });

const statCards = [
  {
    label: 'Total Records',
    value: props.stats.total_records,
    icon: FileText,
    container: 'bg-blue-50 text-blue-600 dark:bg-blue-950 dark:text-blue-400',
    description: 'All jurisprudence entries',
  },
  {
    label: 'Added This Month',
    value: props.stats.records_this_month,
    icon: Calendar,
    container: 'bg-violet-50 text-violet-600 dark:bg-violet-950 dark:text-violet-400',
    description: 'Records created in ' + monthName,
  },
  {
    label: 'With PDF',
    value: props.stats.records_with_pdf,
    icon: CheckCircle,
    container: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400',
    description: 'Cases with PDF availability',
  },
  {
    label: 'Without PDF',
    value: props.stats.records_without_pdf,
    icon: XCircle,
    container: 'bg-orange-50 text-orange-600 dark:bg-orange-950 dark:text-orange-400',
    description: 'Cases without PDF',
  },
];
</script>

<template>
  <Head title="Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 md:p-4">
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <Card v-for="card in statCards" :key="card.label">
          <CardHeader class="flex flex-row items-start justify-between gap-4 space-y-0 pb-4">
            <div class="min-w-0 flex-1">
              <CardTitle class="text-sm font-medium text-muted-foreground">
                {{ card.label }}
              </CardTitle>
              <div class="mt-1 flex items-baseline gap-1.5">
                <span class="text-2xl font-bold tracking-tight">{{ card.value }}</span>
              </div>
              <p class="mt-0.5 text-xs text-muted-foreground truncate">{{ card.description }}</p>
            </div>
            <div
              class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border"
              :class="card.container"
            >
              <component :is="card.icon" class="h-5 w-5" />
            </div>
          </CardHeader>
        </Card>
      </div>

      <div class="grid gap-6 lg:grid-cols-3">
        <Card class="lg:col-span-1">
          <CardHeader>
            <div>
              <CardTitle class="text-base">Recent Activity</CardTitle>
              <CardDescription>Latest system activities</CardDescription>
            </div>
          </CardHeader>
          <CardContent class="p-0">
            <div v-if="recentActivities.length > 0" class="divide-y">
              <div
                v-for="activity in recentActivities"
                :key="activity.id"
                class="flex items-start gap-3 px-6 py-3.5 transition-colors hover:bg-muted/30"
              >
                <div class="min-w-0 flex-1">
                  <p class="text-sm font-medium leading-snug line-clamp-2">
                    {{ activity.description }}
                  </p>
                  <div class="mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-muted-foreground">
                    <Badge
                      :class="[getEventConfig(activity.event).badge, 'capitalize border-0 text-[10px] leading-normal px-1.5 py-0']"
                    >
                      {{ activity.event }}
                    </Badge>
                    <span>{{ activity.causer?.name || 'System' }}</span>
                    <span aria-hidden="true">&middot;</span>
                    <span>{{ formatDateTime(activity.created_at) }}</span>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="px-6 py-8 text-center text-sm text-muted-foreground">
              No recent activity.
            </div>
          </CardContent>
        </Card>

        <Card class="lg:col-span-2">
          <CardHeader>
            <div>
              <CardTitle class="text-base">Recent Jurisprudence</CardTitle>
              <CardDescription>Latest cases added to the system</CardDescription>
            </div>
          </CardHeader>
          <CardContent class="p-0">
            <div v-if="recentJurisprudence.length > 0">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>G.R. Number</TableHead>
                    <TableHead>Date</TableHead>
                    <TableHead>Ponente</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="j in recentJurisprudence" :key="j.id">
                    <TableCell>
                      <div class="font-medium">{{ j.gr_number }}</div>
                      <div
                        v-if="j.citation"
                        class="mt-0.5 max-w-md truncate text-xs text-muted-foreground"
                      >
                        {{ j.citation }}
                      </div>
                    </TableCell>
                    <TableCell class="text-muted-foreground">
                      {{ j.date ? formatDate(j.date) : '—' }}
                    </TableCell>
                    <TableCell class="text-muted-foreground">
                      {{ j.ponente || '—' }}
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>
            <div v-else class="px-6 py-8 text-center text-sm text-muted-foreground">
              No jurisprudence records yet.
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
