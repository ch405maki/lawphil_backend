<template>
  <Table>
    <TableHeader>
      <TableRow>
        <TableHead>Picture</TableHead>
        <TableHead>Full Name</TableHead>
        <TableHead>Email</TableHead>
        <TableHead>Role</TableHead>
        <TableHead>Status</TableHead>
        <TableHead class="text-right">Action</TableHead>
      </TableRow>
    </TableHeader>
    <TableBody>
      <TableRow v-for="user in users" :key="user.id">
        <!-- Profile Picture -->
        <TableCell>
          <img
            v-if="user.profile_picture"
            :src="`/storage/${user.profile_picture.file_path}`"
            alt="Profile"
            class="h-10 w-10 rounded-full object-cover border"
          />
          <div v-else class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-xs text-gray-500">
            N/A
          </div>
        </TableCell>

        <!-- Full Name -->
        <TableCell class="font-medium">{{ user.name }}</TableCell>

        <!-- Email -->
        <TableCell>{{ user.email }}</TableCell>

        <!-- Role -->
        <TableCell>{{ user.role }}</TableCell>

        <!-- Status Switch -->
        <TableCell>
          <CustomSwitch
            :checked="user.status === 'active'"
            @update:checked="(checked) => handleToggle(user, checked)"
          />
        </TableCell>

        <!-- Actions -->
        <TableCell class="text-right space-x-2">
          <EditUserDialog v-if="can('users', 'update')" :user="user" />
          <DeleteUserDialog v-if="can('users', 'delete')" :user="user" />
        </TableCell>
      </TableRow>
    </TableBody>
  </Table>
</template>


<script setup lang="ts">
import { ref } from "vue";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import EditUserDialog from "@/components/users/EditUserDialog.vue";
import DeleteUserDialog from "@/components/users/DeleteUserDialog.vue";
import CustomSwitch from '@/components/ui/customswitch/CustomSwitch.vue';
import { Switch } from '@/components/ui/switch'
import axios from 'axios';
import { useToast } from 'vue-toastification';
import { usePermissions } from '@/composables/usePermissions';

const { can } = usePermissions();

// Define the User type
interface User {
  id: number;
  name: string;
  email: string;
  role: string;
  status: string;
}

// Define props
const props = defineProps<{ users: User[] }>();

// Toast
const toast = useToast();

// Local copy of users for reactivity
const localUsers = ref<User[]>([...props.users]);

// Toggle User Status
const handleToggle = async (user: User, checked: boolean) => {
  try {
    // Determine the new status
    const newStatus = checked ? "active" : "inactive";

    console.log("Sending PATCH request to update status...");
    console.log("User ID:", user.id);
    console.log("New Status:", newStatus);

    // Send update request to the new endpoint
    const response = await axios.patch(`/api/users/${user.id}/status`, {
      status: newStatus,
    });

    console.log("API Response:", response.data);

    // Update the local state
    const userIndex = localUsers.value.findIndex(u => u.id === user.id);
    if (userIndex !== -1) {
      localUsers.value[userIndex].status = newStatus;
    }

    toast.success(`User status updated to ${newStatus}`);
  } catch (error) {
    console.error("Error updating user status:", error);
    toast.error("Failed to update user status.");
  }
};
</script>