<script setup lang="ts">
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Button } from '@/components/ui/button';
import { 
  InfoIcon, 
  FileSpreadsheet, 
  Upload, 
  Calendar,
  Lightbulb,
  Download
} from 'lucide-vue-next';
import axios from 'axios';

interface InfoItem {
  title: string;
  description: string;
  icon?: any;
}

const props = defineProps<{
  title?: string;
  description?: string;
  items?: InfoItem[];
  buttonText?: string;
  buttonVariant?: 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link';
  buttonSize?: 'default' | 'sm' | 'lg' | 'icon';
}>();

// Download template
const downloadTemplate = async () => {
    try {
        const response = await axios.get('/api/v1/mo/import/template', {
            responseType: 'blob'
        });
        
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'mo_template.xlsx');
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
        
    } catch (error) {
        console.error('Template download failed:', error);
        alert('Failed to download template');
    }
};

const defaultItems: InfoItem[] = [
  {
    title: 'Template Format',
    description: 'Use our Excel template with the correct column headers. Required fields: Memorandum Order Number and Date.',
    icon: FileSpreadsheet
  },
  {
    title: 'Bulk Import',
    description: 'Import multiple records at once. Maximum file size: 10MB. Supports .xlsx, .xls, .csv formats.',
    icon: Upload
  },
  {
    title: 'Quick Tips',
    description: 'Empty optional fields become NULL. PDF Availability defaults to 0/No if blank. No URL validation.',
    icon: Lightbulb
  },
  {
    title: 'Date Format',
    description: 'Use YYYY-MM-DD format (e.g., 2024-01-15) for date fields.',
    icon: Calendar
  }
];

const itemsToShow = props.items || defaultItems;
const buttonTextValue = props.buttonText || 'Quick Guide';
const buttonVariantValue = props.buttonVariant || 'outline';
</script>

<template>
  <Popover>
    <PopoverTrigger as-child>
      <Button :variant="buttonVariantValue">
        <InfoIcon class="h-4 w-4" />
        {{ buttonTextValue }}
      </Button>
    </PopoverTrigger>
    <PopoverContent class="w-96 p-0" align="end">
      <div class="p-4 border-b bg-muted/50">
        <h4 class="font-semibold">{{ title || 'Import Quick Guide' }}</h4>
        <p v-if="description" class="text-sm text-muted-foreground mt-1">
          {{ description }}
        </p>
        <p v-else class="text-sm text-muted-foreground mt-1">
          Everything you need to know about importing memorandum order records
        </p>
      </div>
      <div class="p-4 space-y-4 max-h-[60vh] overflow-y-auto">
        <div v-for="(item, index) in itemsToShow" :key="index" class="flex gap-3">
          <div class="flex-shrink-0">
            <component 
              :is="item.icon" 
              class="h-4 w-4 text-primary mt-0.5" 
            />
          </div>
          <div class="space-y-1 flex-1">
            <div class="font-medium text-sm">{{ item.title }}</div>
            <p class="text-sm text-muted-foreground">{{ item.description }}</p>
          </div>
        </div>
        
        <!-- Download Template Section -->
        <div class="flex gap-3 pt-2 border-t">
          <div class="flex-shrink-0">
            <Download class="h-4 w-4 text-primary mt-0.5" />
          </div>
          <div class="space-y-1 flex-1">
            <div class="font-medium text-sm">Download Template</div>
            <p class="text-sm text-muted-foreground">
              Download the template for the exact format required
            </p>
            <Button 
              variant="link" 
              size="sm" 
              class="p-0 h-auto text-primary hover:underline"
              @click="downloadTemplate"
            >
              Download Template.xlsx
            </Button>
          </div>
        </div>
      </div>
    </PopoverContent>
  </Popover>
</template>