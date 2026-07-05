import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

interface ModulePermissions {
    view: boolean
    create: boolean
    update: boolean
    delete: boolean
}

export function usePermissions() {
    const user = computed(() => (usePage().props.auth as any)?.user)
    const permissions = computed<Record<string, ModulePermissions>>(
        () => (usePage().props.auth as any)?.permissions ?? {}
    )

    const isAdmin = computed(() => user.value?.role === 'admin')

    function can(module: string, action: 'view' | 'create' | 'update' | 'delete'): boolean {
        if (isAdmin.value) return true
        return permissions.value[module]?.[action] ?? false
    }

    return { can, isAdmin, permissions }
}
