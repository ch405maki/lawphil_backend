<script setup lang="ts">
import { ref, watch } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';

// Shadcn UI Components
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';

// Icons
import { Loader2, Save } from 'lucide-vue-next';

interface OrderData {
  id: number | null;
  ao_number: string;
  date: string;
  description: string; // Used for "Title"
  subject: string;     // Used for "Category"
  url: string;
  pdf_availability?: boolean;
  pdf_path: string;
}

interface ValidationErrors {
  ao_number?: string[];
  date?: string[];
  description?: string[];
  subject?: string[];
  url?: string[];
  pdf_path?: string[];
}

const props = defineProps<{
  open: boolean;
  orderData: OrderData;
}>();

const emit = defineEmits<{
  (e: 'update:open', value: boolean): void;
  (e: 'saved'): void;
}>();

const processing = ref(false);
const errors = ref<ValidationErrors>({});

const subjectOptions = [
  'General Administration and Personnel',
  'Fiscal and Budgetary Matters',
  'Public Procurement and Asset Management',
  'Regulatory Policy and Licensing',
  'Institutional Organization and Mandates',
  'Public Health and Safety',
  'Environmental and Natural Resources',
  'Information and Communications Technology',
  'Emergency and Contingency Directives',
  'Inter-Agency Coordination'
];

const formData = ref({
  ao_number: '',
  date: '',
  description: '',
  subject: '',
  url: '',
  pdf_availability: false,
  pdf_path: '',
});

// Watch for orderData changes to populate form
watch(() => props.orderData, (newData) => {
  if (newData && newData.id) {
    let formattedDate = '';
    if (newData.date) {
      const dateObj = new Date(newData.date);
      if (!isNaN(dateObj.getTime())) {
        formattedDate = dateObj.toISOString().split('T')[0];
      }
    }

    formData.value = {
      ao_number: newData.ao_number || '',
      date: formattedDate,
      description: newData.description || '',
      subject: newData.subject || '',
      url: newData.url || '',
      pdf_availability: !!newData.pdf_availability,
      pdf_path: newData.pdf_path || '',
    };
  }
}, { immediate: true });

const updateOrder = async () => {
  errors.value = {};
  processing.value = true;

  try {
    const response = await axios.post(
      `/api/v1/administrative/${props.orderData.id}`,
      {
        ...formData.value,
        _method: 'PUT'
      },
      {
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        }
      }
    );

    if (response.data.success) {
      toast.success('Administrative Order updated successfully!');
      emit('update:open', false);
      emit('saved');
    }
  } catch (error: any) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors;
      toast.error('Please check the form for errors');
    } else {
      toast.error(error.response?.data?.message || 'Failed to update order');
    }
  } finally {
    processing.value = false;
  }
};

const closeDialog = () => {
  if (!processing.value) {
    emit('update:open', false);
    errors.value = {};
  }
};
</script>

<template>
  <Dialog :open="open" @update:open="closeDialog">
    <DialogContent class="max-w-3xl max-h-[95vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Edit Administrative Order</DialogTitle>
        <DialogDescription>
          Update the administrative order details below.
        </DialogDescription>
      </DialogHeader>
      
      <div class="space-y-4 py-4">
        <div class="grid grid-cols-1 w-full">
          <div class="space-y-2">
            <Label for="description">
              Description / Title <span class="text-destructive">*</span>
            </Label>
            <Input
              id="description"
              v-model="formData.description"
              placeholder="e.g., AUTHORIZING THE GRANT OF GRATUITY PAY..."
              :disabled="processing"
              :class="{ 'border-destructive': errors.description }"
              class="w-full"
            />
            <p v-if="errors.description" class="text-xs text-destructive">{{ errors.description[0] }}</p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label for="ao_number">
              Administrative Order No. <span class="text-destructive">*</span>
            </Label>
            <Input
              id="ao_number"
              v-model="formData.ao_number"
              placeholder="e.g., A.O. No. 01"
              :disabled="processing"
              :class="{ 'border-destructive': errors.ao_number }"
            />
            <p v-if="errors.ao_number" class="text-xs text-destructive">{{ errors.ao_number[0] }}</p>
          </div>
          
          <div class="space-y-2">
            <Label for="date">
              Date <span class="text-destructive">*</span>
            </Label>
            <Input
              id="date"
              v-model="formData.date"
              type="date"
              :disabled="processing"
              :class="{ 'border-destructive': errors.date }"
            />
            <p v-if="errors.date" class="text-xs text-destructive">{{ errors.date[0] }}</p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label for="url">Source URL</Label>
            <Input
              id="url"
              v-model="formData.url"
              type="text"
              placeholder="Official Gazette Link"
              :disabled="processing"
              :class="{ 'border-destructive': errors.url }"
            />
            <p v-if="errors.url" class="text-xs text-destructive">{{ errors.url[0] }}</p>
          </div>
          <div class="space-y-2">
            <Label for="subject">Subject Area</Label>
            <select
              id="subject"
              v-model="formData.subject"
              :disabled="processing"
              class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
              :class="{ 'border-destructive': errors.subject }"
            >
              <option value="" disabled>Select category</option>
              <option v-for="option in subjectOptions" :key="option" :value="option">
                {{ option }}
              </option>
            </select>
            <p v-if="errors.subject" class="text-xs text-destructive">{{ errors.subject[0] }}</p>
          </div>
        </div>

        <div class="space-y-2 pt-2">
          <div class="flex items-start gap-4 p-3 border rounded-md">
            <div class="flex items-center space-x-2 pt-2">
              <Checkbox
                id="pdf_availability"
                v-model:checked="formData.pdf_availability"
                :disabled="processing"
              />
              <Label for="pdf_availability" class="cursor-pointer font-medium">
                PDF Available
              </Label>
            </div>
            
            <div class="flex-1 space-y-2">
              <Input
                id="pdf_path"
                v-model="formData.pdf_path"
                type="text"
                placeholder="e.g., /storage/ao/41.pdf"
                :disabled="processing || !formData.pdf_availability"
                :class="{ 'border-destructive': errors.pdf_path }"
                class="w-full"
              />
              <p v-if="errors.pdf_path" class="text-xs text-destructive">{{ errors.pdf_path[0] }}</p>
            </div>
          </div>
        </div>
      </div>

      <DialogFooter class="border-t pt-6">
        <div class="flex w-full justify-between items-center">
          <p class="text-xs text-muted-foreground italic">
            <span class="text-destructive">*</span> Required fields.
          </p>
          <div class="flex gap-2">
            <Button variant="outline" @click="closeDialog" :disabled="processing">
              Cancel
            </Button>
            <Button @click="updateOrder" :disabled="processing" class="gap-2">
              <Loader2 v-if="processing" class="h-4 w-4 animate-spin" />
              <Save v-else class="h-4 w-4" />
              {{ processing ? 'Updating...' : 'Save Changes' }}
            </Button>
          </div>
        </div>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>