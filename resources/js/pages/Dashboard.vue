<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
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
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';

import {
  Calendar,
  CheckCircle,
  XCircle,
  Activity,
  Plus,
  Pencil,
  Trash2,
  ArrowRight,
  Library,
  Download,
} from 'lucide-vue-next';

interface ModuleBreakdown {
  key: string;
  label: string;
  total: number;
  this_month: number;
  with_pdf: number;
  without_pdf: number;
}

interface DashboardStats {
  total_records: number;
  records_this_month: number;
  records_with_pdf: number;
  records_without_pdf: number;
  pdf_coverage_rate: number;
}

interface RecentRecord {
  id: number;
  module: string;
  module_label: string;
  identifier: string;
  citation: string;
  date: string;
  pdf_availability: boolean;
  created_at: string;
}

interface ActivityItem {
  id: number;
  description: string;
  event: string;
  log_name: string;
  causer: { id: number; name: string; email: string } | null;
  created_at: string;
}

const props = defineProps<{
  stats: DashboardStats;
  moduleBreakdown: ModuleBreakdown[];
  recentRecords: RecentRecord[];
  recentActivities: ActivityItem[];
  selectedModule: string;
  currentMonth: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
];

const user = usePage().props.auth.user as { name: string } | null;

const greeting = computed(() => {
  const hour = new Date().getHours();
  if (hour < 12) return 'Good morning';
  if (hour < 18) return 'Good afternoon';
  return 'Good evening';
});

const selectedModule = ref(props.selectedModule);

function onModuleChange(value: string) {
  selectedModule.value = value;
  router.get('/dashboard', { module: value === 'all' ? undefined : value }, { preserveState: true });
}

const filteredBreakdown = computed(() => {
  if (selectedModule.value === 'all') return props.moduleBreakdown;
  return props.moduleBreakdown.filter(m => m.key === selectedModule.value);
});

const filteredStats = computed(() => {
  if (selectedModule.value === 'all') return props.stats;
  const m = props.moduleBreakdown.find(m => m.key === selectedModule.value);
  if (!m) return props.stats;
  return {
    total_records: m.total,
    records_this_month: m.this_month,
    records_with_pdf: m.with_pdf,
    records_without_pdf: m.without_pdf,
    pdf_coverage_rate: m.total > 0 ? Math.round((m.with_pdf / m.total) * 100) : 0,
  };
});

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

const moduleColors: Record<string, string> = {
  jurisprudence: 'bg-blue-500',
  presidential: 'bg-red-500',
  proclamation: 'bg-purple-500',
  republic: 'bg-emerald-500',
  execord: 'bg-amber-500',
  ao: 'bg-cyan-500',
  mo: 'bg-pink-500',
  mc: 'bg-indigo-500',
  genor: 'bg-teal-500',
  acts: 'bg-orange-500',
  batas_pambansa: 'bg-rose-500',
  commonwealth: 'bg-violet-500',
};

const moduleHrefMap: Record<string, string> = {
  jurisprudence: '/jurisprudence',
  presidential: '/presidential',
  proclamation: '/proclamation',
  republic: '/republic',
  execord: '/execord',
  ao: '/ao',
  mo: '/mo',
  mc: '/mc',
  genor: '/genor',
  acts: '/acts',
  batas_pambansa: '/batas-pambansa',
  commonwealth: '/commonwealth',
};

const totalForChart = computed(() => props.stats.total_records);
</script>

<template>
  <Head title="Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 md:p-4">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">{{ greeting }}, {{ user?.name || 'User' }}</h1>
        <p class="text-sm text-muted-foreground">Dashboard — overview of all legal document modules</p>
      </div>
      <div class="flex justify-end min-w-[420px]">
        <Select :model-value="selectedModule" @update:model-value="onModuleChange" class="flex-shrink-0 w-28"> <!-- Even smaller -->
        <SelectTrigger class="w-full text-sm"> <!-- Smaller text inside -->
          <SelectValue placeholder="All Modules" />
        </SelectTrigger>
        <SelectContent>
          <SelectItem value="all">All Modules</SelectItem>
          <SelectItem v-for="m in moduleBreakdown" :key="m.key" :value="m.key">
            {{ m.label }}
          </SelectItem>
        </SelectContent>
      </Select>
      </div>
    </div>

      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <Card>
          <CardHeader class="flex flex-row items-start justify-between gap-4 space-y-0 pb-4">
            <div class="min-w-0 flex-1">
              <CardTitle class="text-sm font-medium text-muted-foreground">Total Records</CardTitle>
              <div class="mt-1 flex items-baseline gap-1.5">
                <span class="text-2xl font-bold tracking-tight">{{ filteredStats.total_records.toLocaleString() }}</span>
              </div>
              <p class="mt-0.5 text-xs text-muted-foreground truncate">
                {{ selectedModule === 'all' ? 'Across all 12 modules' : filteredBreakdown.length > 0 ? filteredBreakdown[0].label : '' }} records
              </p>
            </div>
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border bg-blue-50 text-blue-600 dark:bg-blue-950 dark:text-blue-400">
              <Library class="h-5 w-5" />
            </div>
          </CardHeader>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-start justify-between gap-4 space-y-0 pb-4">
            <div class="min-w-0 flex-1">
              <CardTitle class="text-sm font-medium text-muted-foreground">Added This Month</CardTitle>
              <div class="mt-1 flex items-baseline gap-1.5">
                <span class="text-2xl font-bold tracking-tight">{{ filteredStats.records_this_month.toLocaleString() }}</span>
              </div>
              <p class="mt-0.5 text-xs text-muted-foreground truncate">Records created in {{ currentMonth }}</p>
            </div>
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border bg-violet-50 text-violet-600 dark:bg-violet-950 dark:text-violet-400">
              <Calendar class="h-5 w-5" />
            </div>
          </CardHeader>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-start justify-between gap-4 space-y-0 pb-4">
            <div class="min-w-0 flex-1">
              <CardTitle class="text-sm font-medium text-muted-foreground">With PDF</CardTitle>
              <div class="mt-1 flex items-baseline gap-1.5">
                <span class="text-2xl font-bold tracking-tight">{{ filteredStats.records_with_pdf.toLocaleString() }}</span>
              </div>
              <p class="mt-0.5 text-xs text-muted-foreground truncate">Documents with PDF available</p>
            </div>
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border bg-emerald-50 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400">
              <Download class="h-5 w-5" />
            </div>
          </CardHeader>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-start justify-between gap-4 space-y-0 pb-4">
            <div class="min-w-0 flex-1">
              <CardTitle class="text-sm font-medium text-muted-foreground">PDF Coverage</CardTitle>
              <div class="mt-1 flex items-baseline gap-1.5">
                <span class="text-2xl font-bold tracking-tight">{{ filteredStats.pdf_coverage_rate }}%</span>
              </div>
              <div class="mt-2 h-2 w-full rounded-full bg-muted overflow-hidden">
                <div
                  class="h-full rounded-full bg-emerald-500 transition-all duration-500"
                  :style="{ width: filteredStats.pdf_coverage_rate + '%' }"
                />
              </div>
              <p class="mt-0.5 text-xs text-muted-foreground truncate">{{ filteredStats.records_without_pdf.toLocaleString() }} records missing PDF</p>
            </div>
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border bg-orange-50 text-orange-600 dark:bg-orange-950 dark:text-orange-400">
              <XCircle class="h-5 w-5" />
            </div>
          </CardHeader>
        </Card>
      </div>

      <div class="grid gap-6 lg:grid-cols-3">
        <Card class="lg:col-span-1">
          <CardHeader class="flex flex-row items-center justify-between">
            <div>
              <CardTitle class="text-base">Module Breakdown</CardTitle>
              <CardDescription>Records per document type</CardDescription>
            </div>
          </CardHeader>
          <CardContent class="p-0">
            <div class="divide-y">
              <div
                v-for="m in filteredBreakdown"
                :key="m.key"
                class="flex items-center justify-between px-6 py-3 transition-colors hover:bg-muted/30"
              >
                <div class="flex items-center gap-3 min-w-0 flex-1">
                  <div class="h-2.5 w-2.5 shrink-0 rounded-full" :class="moduleColors[m.key] || 'bg-gray-400'" />
                  <span class="text-sm font-medium truncate">{{ m.label }}</span>
                </div>
                <div class="flex items-center gap-4 shrink-0">
                  <span class="text-sm tabular-nums font-semibold">{{ m.total.toLocaleString() }}</span>
                  <Badge v-if="m.this_month > 0" variant="outline" class="text-[10px] leading-normal px-1.5 py-0 border-green-200 text-green-700 dark:border-green-800 dark:text-green-400">
                    +{{ m.this_month }}
                  </Badge>
                  <Link v-if="moduleHrefMap[m.key]" :href="moduleHrefMap[m.key]" class="text-muted-foreground hover:text-primary transition-colors">
                    <ArrowRight class="h-4 w-4" />
                  </Link>
                </div>
              </div>
              <div v-if="filteredBreakdown.length === 0" class="px-6 py-8 text-center text-sm text-muted-foreground">
                No data for this module.
              </div>
            </div>
          </CardContent>
        </Card>

        <Card class="lg:col-span-2">
          <CardHeader class="flex flex-row items-center justify-between">
            <div>
              <CardTitle class="text-base">Recent Records</CardTitle>
              <CardDescription>Latest entries across all modules</CardDescription>
            </div>
            <Link href="/jurisprudence" class="inline-flex items-center gap-1 text-sm font-medium text-primary hover:underline">
              View All <ArrowRight class="h-3 w-3" />
            </Link>
          </CardHeader>
          <CardContent class="p-0">
            <div v-if="recentRecords.length > 0">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Reference</TableHead>
                    <TableHead>Module</TableHead>
                    <TableHead>Date</TableHead>
                    <TableHead class="text-center">PDF</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="r in recentRecords" :key="`${r.module}-${r.id}`">
                    <TableCell>
                      <div class="font-medium">{{ r.identifier }}</div>
                      <div v-if="r.citation" class="mt-0.5 max-w-xs truncate text-xs text-muted-foreground">
                        {{ r.citation }}
                      </div>
                    </TableCell>
                    <TableCell>
                      <Badge variant="outline" class="text-[10px] leading-normal px-1.5 py-0">
                        {{ r.module_label }}
                      </Badge>
                    </TableCell>
                    <TableCell class="text-muted-foreground text-sm">
                      {{ r.date ? formatDate(r.date) : '—' }}
                    </TableCell>
                    <TableCell class="text-center">
                      <CheckCircle v-if="r.pdf_availability" class="inline h-4 w-4 text-emerald-500" />
                      <XCircle v-else class="inline h-4 w-4 text-muted-foreground" />
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>
            <div v-else class="px-6 py-8 text-center text-sm text-muted-foreground">
              No records found.
            </div>
          </CardContent>
        </Card>
      </div>

      <div class="grid gap-6 lg:grid-cols-3">
        <Card class="lg:col-span-1">
          <CardHeader class="flex flex-row items-center justify-between">
            <div>
              <CardTitle class="text-base">Recent Activity</CardTitle>
              <CardDescription>Latest system activities</CardDescription>
            </div>
            <Link href="/logs" class="inline-flex items-center gap-1 text-sm font-medium text-primary hover:underline">
              View All <ArrowRight class="h-3 w-3" />
            </Link>
          </CardHeader>
          <CardContent class="p-0">
            <div v-if="recentActivities.length > 0" class="divide-y">
              <div
                v-for="activity in recentActivities"
                :key="activity.id"
                class="flex items-start gap-3 px-6 py-3.5 transition-colors hover:bg-muted/30"
              >
                <div
                  class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg"
                  :class="getEventConfig(activity.event).container"
                >
                  <component :is="getEventConfig(activity.event).icon" class="h-3.5 w-3.5" />
                </div>
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
              <CardTitle class="text-base">Module Distribution</CardTitle>
              <CardDescription>Proportion of records per module</CardDescription>
            </div>
          </CardHeader>
          <CardContent>
            <div v-if="totalForChart > 0" class="space-y-3">
              <div
                v-for="m in moduleBreakdown.filter(m => m.total > 0)"
                :key="m.key"
                class="space-y-1"
              >
                <div class="flex items-center justify-between text-sm">
                  <div class="flex items-center gap-2 min-w-0 flex-1">
                    <span class="h-2.5 w-2.5 shrink-0 rounded-full" :class="moduleColors[m.key] || 'bg-gray-400'" />
                    <span class="font-medium truncate">{{ m.label }}</span>
                  </div>
                  <div class="flex items-center gap-3 shrink-0">
                    <span class="text-xs text-muted-foreground">{{ Math.round((m.total / totalForChart) * 100) }}%</span>
                    <span class="tabular-nums font-semibold w-16 text-right">{{ m.total.toLocaleString() }}</span>
                  </div>
                </div>
                <div class="h-2 w-full rounded-full bg-muted overflow-hidden">
                  <div
                    class="h-full rounded-full transition-all duration-500"
                    :class="moduleColors[m.key] || 'bg-gray-400'"
                    :style="{ width: (m.total / Math.max(...moduleBreakdown.map(x => x.total))) * 100 + '%' }"
                  />
                </div>
              </div>
            </div>
            <div v-else class="py-8 text-center text-sm text-muted-foreground">
              No records yet.
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
</style>
