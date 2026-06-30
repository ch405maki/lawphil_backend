<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { formatDateTime } from '@/lib/utils';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: 'Activity Logs',
    href: '#',
  },
];

interface ActivityLog {
  id: number;
  log_name: string;
  description: string;
  event: string;
  created_at: string;
  causer: {
    id: number;
    name: string;
    email: string;
    role: string;
  };
  properties?: {
    old?: any;
    new?: any;
  };
}

const logs = ref<ActivityLog[]>([]);
const loading = ref(true);
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0
});

const getEventColor = (event: string) => {
  const colors: Record<string, string> = {
    created: 'bg-green-100 text-green-800',
    updated: 'bg-blue-100 text-blue-800',
    deleted: 'bg-red-100 text-red-800',
    restored: 'bg-purple-100 text-purple-800',
  };
  return colors[event] || 'bg-gray-100 text-gray-800';
};

const getLogNameColor = (logName: string) => {
  const colors: Record<string, string> = {
    jurisprudence: 'bg-indigo-100 text-indigo-800',
    appointment: 'bg-emerald-100 text-emerald-800',
    user: 'bg-cyan-100 text-cyan-800',
  };
  return colors[logName] || 'bg-gray-100 text-gray-800';
};

const fetchActivityLogs = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get('/api/activity-logs', {
      params: { page }
    });
    
    logs.value = response.data.data;
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      total: response.data.total
    };
  } catch (error) {
    console.error('Error fetching activity logs:', error);
  } finally {
    loading.value = false;
  }
};

const changePage = (page: number) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchActivityLogs(page);
  }
};

onMounted(() => {
  fetchActivityLogs();
});
</script>

<template>
  <Head title="Activity Logs" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <!-- Header with title -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold">Activity Logs</h1>
          <p class="text-sm text-muted-foreground">Track all system activities and changes</p>
        </div>
        <Button @click="fetchActivityLogs" variant="outline" size="sm">
          Refresh
        </Button>
      </div>

      <!-- Activity Logs Table -->
      <div class="rounded-lg border">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Event</TableHead>
              <TableHead>Description</TableHead>
              <TableHead>User</TableHead>
              <TableHead>Module</TableHead>
              <TableHead>Date & Time</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-if="loading">
              <TableCell colspan="6" class="text-center py-8">
                <div class="flex justify-center items-center">
                  <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
                  <span class="ml-2">Loading...</span>
                </div>
              </TableCell>
            </TableRow>
            <TableRow v-else-if="logs.length === 0">
              <TableCell colspan="6" class="text-center py-8 text-muted-foreground">
                No activity logs found
              </TableCell>
            </TableRow>
            <TableRow v-for="log in logs" :key="log.id">
              <TableCell>
                <Badge :class="getEventColor(log.event)" class="capitalize">
                  {{ log.event }}
                </Badge>
              </TableCell>
              <TableCell class="max-w-md">
                <span class="text-sm">{{ log.description }}</span>
              </TableCell>
              <TableCell>
                <div class="flex flex-col">
                  <span class="text-sm font-medium">{{ log.causer?.name || 'System' }}</span>
                  <span class="text-xs text-muted-foreground">{{ log.causer?.email || 'N/A' }}</span>
                </div>
              </TableCell>
              <TableCell>
                <Badge :class="getLogNameColor(log.log_name)" class="capitalize">
                  {{ log.log_name }}
                </Badge>
              </TableCell>
              <TableCell>
                <div class="text-sm">{{ formatDateTime(log.created_at) }}</div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="flex justify-between items-center mt-4">
        <div class="text-sm text-muted-foreground">
          Showing {{ ((pagination.current_page - 1) * pagination.per_page) + 1 }} to 
          {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }} of 
          {{ pagination.total }} entries
        </div>
        <div class="flex gap-2">
          <Button
            variant="outline"
            size="sm"
            @click="changePage(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
          >
            Previous
          </Button>
          <div class="flex items-center gap-1">
            <Button
              v-for="page in pagination.last_page"
              :key="page"
              variant="outline"
              size="sm"
              @click="changePage(page)"
              :class="{ 'bg-primary text-primary-foreground': pagination.current_page === page }"
            >
              {{ page }}
            </Button>
          </div>
          <Button
            variant="outline"
            size="sm"
            @click="changePage(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
          >
            Next
          </Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>