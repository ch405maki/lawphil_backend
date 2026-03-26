<script setup lang="ts">
import { ref } from "vue";
import axios from "axios";
import { useToast } from "vue-toastification";
import { Button } from "@/components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger
} from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger
} from "@/components/ui/select";
import { UserRoundPlus } from "lucide-vue-next";

defineProps<{
  profilePictures: { id: number; file_path: string; file_name: string }[];
}>();

const toast = useToast();
const isOpen = ref(false);
const loading = ref(false);
const formData = ref({
  name: "",
  email: "",
  password: "",
  role: "user",
  status: "active",
  profile_picture_id: null as number | null,
});

const openDialog = () => (isOpen.value = true);
const closeDialog = () => (isOpen.value = false);

const createUser = async () => {
  loading.value = true;
  try {
    await axios.post("/api/users", {
      ...formData.value,
      profile_picture_id: formData.value.profile_picture_id
        ? Number(formData.value.profile_picture_id)
        : null,
    });
    toast.success("User created successfully!");
    setTimeout(() => location.reload(), 2000);
  } catch (error) {
    toast.error("Failed to create user");
  } finally {
    loading.value = false;
    closeDialog();
  }
};
</script>

<template>

  <Dialog v-model:open="isOpen">
    <DialogTrigger as-child>
      <Button @click="openDialog">
        <UserRoundPlus class="w-4 h-4" /> Create
      </Button>
    </DialogTrigger>

    <DialogContent>
      <DialogHeader>
        <DialogTitle class="text-lg font-bold">Create New User</DialogTitle>
        <DialogDescription class="text-sm text-muted-foreground">
          Fill in the details to add a new user.
        </DialogDescription>
      </DialogHeader>

      <form @submit.prevent="createUser">
        <div class="grid gap-4">
          <div class="grid gap-4">
            <div class="grid gap-1">
              <label class="text-sm font-medium">Profile Picture</label>
              <div class="grid grid-cols-4">
                <div
                  v-for="pic in profilePictures"
                  :key="pic.id"
                  class="h-16 w-16 flex items-center justify-center border rounded-full cursor-pointer overflow-hidden"
                  :class="{ 'ring-2 ring-blue-500': formData.profile_picture_id === pic.id }"
                  @click="formData.profile_picture_id = pic.id"
                >
                  <img
                    :src="`/storage/${pic.file_path}`"
                    alt="Profile Pic"
                    class="h-full w-full object-cover"
                  />
                </div>
              </div>
            </div>
          </div>

          <div class="grid gap-1">
            <label for="name" class="text-sm font-medium">Name</label>
            <Input id="name" v-model="formData.name" placeholder="John Doe" required />
          </div>

          <div class="grid gap-1">
            <label for="email" class="text-sm font-medium">Email</label>
            <Input id="email" v-model="formData.email" type="email" placeholder="user@example.com" required />
          </div>

          <div class="grid gap-1">
            <label for="password" class="text-sm font-medium">Password</label>
            <Input id="password" v-model="formData.password" type="password" placeholder="••••••••" required />
          </div>

          <div class="grid gap-1">
            <label class="text-sm font-medium">Role</label>
            <Select v-model="formData.role">
              <SelectTrigger>
                <span class="capitalize">{{ formData.role }}</span>
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="admin">Admin</SelectItem>
                <SelectItem value="user">User</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="grid gap-1">
            <label class="text-sm font-medium">Status</label>
            <Select v-model="formData.status">
              <SelectTrigger>
                <span class="capitalize">{{ formData.status }}</span>
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="active">Active</SelectItem>
                <SelectItem value="inactive">Inactive</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>

        <DialogFooter class="mt-4">
          <Button variant="outline" @click="closeDialog">Cancel</Button>
          <Button type="submit" :disabled="loading">
            <span v-if="loading">Creating...</span>
            <span v-else>Create</span>
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>