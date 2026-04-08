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

// I-update ang form kapag nagbago ang orderData prop (at i-fix ang date format)
watch(() => props.open, (isOpen) => { 
    if (isOpen) {
        form.value = { ...props.orderData }; 
        if (form.value.date) {
            // Ginagawa nating YYYY-MM-DD para mabasa ng HTML5 date input
            form.value.date = new Date(form.value.date).toISOString().split('T')[0];
        }
    }
}, { deep: true });

const saveChanges = async () => {
  loading.value = true;
  try {
    // API endpoint para sa General Orders
    const response = await axios.post(`/api/general-orders/${form.value.id}`, {
        ...form.value,
        _method: 'POST' // Siguraduhing tumutugma ito sa route setup niyo (POST/PUT)
    });

    if (response.data.success) {
      toast.success('General Order updated successfully');
      emit('saved');
      emit('update:open', false);
    }
  } catch (error: any) {
    console.error(error);
    toast.error('Failed to update General Order');
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent class="sm:max-w-2xl max-h-[90vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Edit General Order Information</DialogTitle>
        <DialogDescription>Update the order details below.</DialogDescription>
      </DialogHeader>

      <div class="grid gap-4 py-4">
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label>G.O. Number</Label>
            <Input v-model="form.go_number" placeholder="e.g. 12345" />
          </div>
          <div class="space-y-2">
            <Label>Date</Label>
            <Input type="date" v-model="form.date" />
          </div>
        </div>

        <div class="space-y-2">
          <Label>Order Citation / Title</Label>
          <Textarea v-model="form.citation" rows="3" placeholder="Enter title..." />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label>Signatory</Label>
            <Input v-model="form.signatory" placeholder="e.g. Pres. Ferdinand Marcos Jr." />
          </div>
          <div class="space-y-2">
            <Label>Reference</Label>
            <Input v-model="form.reference" />
          </div>
        </div>

        <div class="space-y-2">
          <Label>Reference URL</Label>
          <Input v-model="form.url" placeholder="https://lawphil.net/..." />
        </div>

        <div class="space-y-2">
          <Label>Subject</Label>
          <Textarea v-model="form.subject" rows="2" />
        </div>

        <div class="flex items-center space-x-2 pt-2">
          <Checkbox 
            id="pdf-go" 
            :checked="form.pdf_availability" 
            @update:checked="(val) => form.pdf_availability = val" 
          />
          <Label for="pdf-go" class="font-medium cursor-pointer">PDF Available</Label>
          <Input 
            v-model="form.pdf_path" 
            class="flex-1 ml-4 h-9" 
            placeholder="/uploads/pdfs/..." 
          />
        </div>
      </div>

      <DialogFooter>
        <Button variant="outline" @click="$emit('update:open', false)">Cancel</Button>
        <Button @click="saveChanges" :disabled="loading" class="min-w-[120px]">
          <Loader2 v-if="loading" class="mr-2 h-4 w-4 animate-spin" />
          Save Changes
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>