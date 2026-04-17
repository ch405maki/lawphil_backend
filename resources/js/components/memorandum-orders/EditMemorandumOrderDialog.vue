<script setup lang="ts">
import { ref, watch } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import { Loader2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import {
  Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle,
} from '@/components/ui/dialog';

const props = defineProps<{ open: boolean; orderData: any; }>();
const emit = defineEmits(['update:open', 'saved']);

const loading = ref(false);
const form = ref({ ...props.orderData });

// I-update ang form kapag nagbago ang orderData prop
watch(() => props.orderData, (newVal) => { form.value = { ...newVal }; }, { deep: true });

const saveChanges = async () => {
  loading.value = true;
  try {
    const response = await axios.post(`/api/memorandum-orders/${form.value.id}`, form.value);
    if (response.data.success) {
      toast.success('Order updated successfully');
      emit('saved');
      emit('update:open', false);
    }
  } catch (error: any) {
    toast.error('Failed to update order');
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent class="sm:max-w-2xl max-h-[90vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Edit Memorandum Order Information</DialogTitle>
        <DialogDescription>Update the order details below.</DialogDescription>
      </DialogHeader>

      <div class="grid gap-4 py-4">
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label>M.O. Number</Label>
            <Input v-model="form.mo_number" />
          </div>
          <div class="space-y-2">
            <Label>Date</Label>
            <Input type="date" v-model="form.date" />
          </div>
        </div>

        <div class="space-y-2">
          <Label>Order Citation / Title</Label>
          <Textarea v-model="form.citation" rows="3" />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label>Signatory</Label>
            <Input v-model="form.signatory" />
          </div>
          <div class="space-y-2">
            <Label>Reference</Label>
            <Input v-model="form.reference" />
          </div>
        </div>

        <div class="space-y-2">
          <Label>Reference URL</Label>
          <Input v-model="form.url" />
        </div>

        <div class="space-y-2">
          <Label>Subject</Label>
          <Textarea v-model="form.subject" rows="2" />
        </div>

        <div class="flex items-center space-x-2 pt-2">
          <Checkbox id="pdf" :checked="form.pdf_availability" @update:checked="form.pdf_availability = $event" />
          <Label for="pdf">PDF Available</Label>
          <Input v-model="form.pdf_path" class="flex-1 ml-4 h-8" placeholder="/uploads/pdfs/..." />
        </div>
      </div>

      <DialogFooter>
        <Button variant="outline" @click="$emit('update:open', false)">Cancel</Button>
        <Button @click="saveChanges" :disabled="loading">
          <Loader2 v-if="loading" class="mr-2 h-4 w-4 animate-spin" />
          Save Changes
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>