<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import { ref, onMounted, computed } from 'vue'
import { Shield, Loader2, Plus } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import { Input } from '@/components/ui/input'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { useToast } from "vue-toastification";
import axios from 'axios'

const toast = useToast();

const breadcrumbs = [
  { title: "Configuration", href: "#" },
  { title: "Permissions", href: "#" },
];

interface Permission {
  id?: number
  role: string
  module: string
  can_view: boolean
  can_create: boolean
  can_update: boolean
  can_delete: boolean
}

const roles = ref<string[]>([])
const modules = ref<string[]>([])
const permissions = ref<Record<string, Record<string, Permission>>>({})
const selectedRole = ref('')
const loading = ref(false)
const saving = ref(false)
const newRoleName = ref('')
const addingRole = ref(false)

const actions = [
  { key: 'can_view', label: 'View' },
  { key: 'can_create', label: 'Create' },
  { key: 'can_update', label: 'Update' },
  { key: 'can_delete', label: 'Delete' },
]

const moduleLabels: Record<string, string> = {
  jurisprudence: 'Jurisprudence',
  presidential: 'Presidential Decrees',
  proclamation: 'Proclamations',
  republic: 'Republic Acts',
  execord: 'Executive Orders',
  ao: 'Administrative Orders',
  mo: 'Memorandum Orders',
  mc: 'Memorandum Circulars',
  genor: 'General Orders',
  users: 'User Management',
  logs: 'Activity Logs',
}

const currentPermissions = computed(() => {
  if (!selectedRole.value) return []
  return modules.value.map(mod => ({
    module: mod,
    label: moduleLabels[mod] || mod,
    can_view: permissions.value[selectedRole.value]?.[mod]?.can_view ?? false,
    can_create: permissions.value[selectedRole.value]?.[mod]?.can_create ?? false,
    can_update: permissions.value[selectedRole.value]?.[mod]?.can_update ?? false,
    can_delete: permissions.value[selectedRole.value]?.[mod]?.can_delete ?? false,
  }))
})

const fetchPermissions = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/api/v1/permissions')
    roles.value = data.roles
    modules.value = data.modules

    const permMap: Record<string, Record<string, Permission>> = {}
    for (const role of data.roles) {
      permMap[role] = {}
      for (const mod of data.modules) {
        const existing = data.permissions[role]?.find((p: Permission) => p.module === mod)
        permMap[role][mod] = existing || { role, module: mod, can_view: false, can_create: false, can_update: false, can_delete: false }
      }
    }
    permissions.value = permMap

    if (!selectedRole.value && data.roles.length > 0) {
      selectedRole.value = data.roles[0]
    }
  } catch (e) {
    toast.error('Failed to load permissions')
  } finally {
    loading.value = false
  }
}

const togglePermission = async (module: string, action: string) => {
  const role = selectedRole.value
  if (!role) return
  const perm = permissions.value[role]?.[module]
  if (!perm) return

  const newValue = !(perm as any)[action]
  ;(perm as any)[action] = newValue

  saving.value = true
  try {
    await axios.post('/api/v1/permissions/update', {
      role,
      module,
      can_view: perm.can_view,
      can_create: perm.can_create,
      can_update: perm.can_update,
      can_delete: perm.can_delete,
    })
  } catch (e) {
    ;(perm as any)[action] = !newValue
    toast.error('Failed to update permission')
  } finally {
    saving.value = false
  }
}

const addRole = async () => {
  if (!newRoleName.value.trim()) return
  addingRole.value = true
  try {
    await axios.post('/api/v1/permissions/add-role', { role: newRoleName.value.trim() })
    toast.success(`Role '${newRoleName.value.trim()}' created`)
    newRoleName.value = ''
    await fetchPermissions()
    selectedRole.value = newRoleName.value.trim()
  } catch (e: any) {
    toast.error(e.response?.data?.errors?.role?.[0] || 'Failed to create role')
  } finally {
    addingRole.value = false
  }
}

onMounted(fetchPermissions)
</script>

<template>
  <Head title="Permissions Management" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="flex justify-between items-center">
        <div class="flex items-center gap-3">
          <Shield class="h-6 w-6 text-primary" />
          <h1 class="text-xl font-bold text-gray-900 dark:text-white">Role Permissions</h1>
        </div>
        <Badge v-if="saving" variant="secondary" class="gap-1">
          <Loader2 class="h-3 w-3 animate-spin" /> Saving...
        </Badge>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <Card class="lg:col-span-1">
          <CardHeader>
            <CardTitle class="text-lg">Roles</CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div v-if="loading" class="flex justify-center py-4">
              <Loader2 class="h-6 w-6 animate-spin text-muted-foreground" />
            </div>

            <template v-else>
              <Select v-model="selectedRole">
                <SelectTrigger>
                  <SelectValue :placeholder="roles.length > 0 ? 'Select a role...' : 'No roles yet'" />
                </SelectTrigger>
                <SelectContent>
                  <SelectGroup>
                    <SelectItem v-for="role in roles" :key="role" :value="role" class="capitalize">
                      {{ role }}
                    </SelectItem>
                  </SelectGroup>
                </SelectContent>
              </Select>

              <div class="pt-2">
                <p class="text-xs text-muted-foreground mb-2">Add new role</p>
                <div class="flex gap-2">
                  <Input v-model="newRoleName" placeholder="Role name" @keyup.enter="addRole" />
                  <Button size="sm" @click="addRole" :disabled="addingRole || !newRoleName.trim()">
                    <Plus class="h-4 w-4" />
                  </Button>
                </div>
              </div>
            </template>
          </CardContent>
        </Card>

        <Card class="lg:col-span-2">
          <CardHeader>
            <CardTitle class="text-lg capitalize" v-if="selectedRole">
              {{ selectedRole }} Permissions
            </CardTitle>
            <CardTitle class="text-lg" v-else>Permissions</CardTitle>
            <p class="text-sm text-muted-foreground">
              <template v-if="selectedRole && selectedRole === 'admin'">
                Admin always has full access. Toggling checkboxes has no effect.
              </template>
              <template v-else-if="selectedRole">
                Check the actions this role can perform on each module.
              </template>
              <template v-else>
                Select a role from the left to manage its permissions.
              </template>
            </p>
          </CardHeader>
          <CardContent>
            <div v-if="!selectedRole" class="flex flex-col items-center justify-center py-12 text-muted-foreground">
              <Shield class="h-12 w-12 mb-3 opacity-30" />
              <p>Select a role to manage its permissions</p>
            </div>

            <div v-else class="overflow-x-auto">
              <table class="w-full border-collapse">
                <thead>
                  <tr class="border-b">
                    <th class="text-left py-3 pr-4 font-semibold text-sm text-muted-foreground w-48">Module</th>
                    <th v-for="action in actions" :key="action.key" class="text-center py-3 px-1 text-xs font-medium text-muted-foreground">
                      {{ action.label }}
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="mod in currentPermissions" :key="mod.module" class="border-b hover:bg-muted/50 transition-colors">
                    <td class="py-3 pr-4 text-sm font-medium">
                      {{ mod.label }}
                    </td>
                    <td v-for="action in actions" :key="`${mod.module}-${action.key}`" class="text-center py-3 px-1">
                      <div class="flex justify-center">
                        <Checkbox
                          :checked="(mod as any)[action.key]"
                          @update:checked="togglePermission(mod.module, action.key)"
                          :disabled="saving || selectedRole === 'admin'"
                        />
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
