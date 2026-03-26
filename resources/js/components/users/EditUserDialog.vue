<template>
  <Sheet>
    <!-- Sheet Trigger -->
    <SheetTrigger as-child>
      <Button
        variant="outline"
        size="icon"
        @click="openDialog"
      >
        <UserRoundPen />
      </Button>
    </SheetTrigger>

    <!-- Sheet Content -->
    <SheetContent class="text-gray-900 dark:text-gray-200">
      <SheetHeader>
        <SheetTitle>Edit User</SheetTitle>
        <SheetDescription>Modify user details and save changes.</SheetDescription>
      </SheetHeader>

      <!-- Edit User Form -->
      <form @submit.prevent="updateUser" class="mt-6">
        <div class="space-y-4">
          <!-- Name Field -->
          <div class="space-y-2">
            <Label for="name">Name</Label>
            <Input
              id="name"
              v-model="userData.name"
              type="text"
              required
            />
          </div>

          <!-- Email Field -->
          <div class="space-y-2">
            <Label for="email">Email</Label>
            <Input
              id="email"
              v-model="userData.email"
              type="email"
              required
            />
          </div>

          <!-- Role Field -->
          <div class="space-y-2">
            <Label for="role">Role</Label>
            <Select v-model="userData.role">
              <SelectTrigger>
                <SelectValue placeholder="Select role" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="admin">Admin</SelectItem>
                <SelectItem value="user">User</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Status Field -->
          <div class="space-y-2">
            <Label for="status">Status</Label>
            <Select v-model="userData.status">
              <SelectTrigger>
                <SelectValue placeholder="Select status" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="active">Active</SelectItem>
                <SelectItem value="inactive">Inactive</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Save Changes Button -->
          <Button type="submit" class="w-full mt-6">
            <span v-if="!isLoading">Save Changes</span>
            <span v-else class="flex items-center gap-2">
              <Loader2 class="h-4 w-4 animate-spin" />
              Saving...
            </span>
          </Button>
        </div>
      </form>
    </SheetContent>
  </Sheet>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { useToast } from "vue-toastification";
import axios from "axios";
import { Loader2 } from "lucide-vue-next";
import {
  Sheet,
  SheetTrigger,
  SheetContent,
  SheetHeader,
  SheetTitle,
  SheetDescription,
} from "@/components/ui/sheet";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { UserRoundPen } from "lucide-vue-next";

const props = defineProps<{
  user: {
    id: number;
    name: string;
    email: string;
    role: string;
    status: string;
  };
}>();

const emit = defineEmits(['updated']);

const toast = useToast();
const isLoading = ref(false);
const isOpen = ref(false);

const userData = ref({
  id: props.user.id,
  name: props.user.name,
  email: props.user.email,
  role: props.user.role,
  status: props.user.status,
});

const openDialog = () => {
  // Reset form data to the current user's data
  userData.value = {
    id: props.user.id,
    name: props.user.name,
    email: props.user.email,
    role: props.user.role,
    status: props.user.status,
  };
  isOpen.value = true;
};

const updateUser = async () => {
  try {
    isLoading.value = true;
    
    const response = await axios.put(`/api/users/${userData.value.id}`, userData.value);
    
    toast.success("User updated successfully!");
    emit('updated', response.data.user);
    isOpen.value = false;
    
  } catch (error) {
    if (error.response?.data?.errors) {
      Object.entries(error.response.data.errors).forEach(([field, messages]) => {
        toast.error(`${field}: ${messages.join(', ')}`);
      });
    } else {
      toast.error(error.response?.data?.message || "Failed to update user");
    }
  } finally {
    isLoading.value = false;
  }
};
</script>