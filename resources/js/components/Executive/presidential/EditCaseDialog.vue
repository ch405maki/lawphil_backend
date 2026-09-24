<script setup lang="ts">
import axios from 'axios';
import { ref, watch } from 'vue';
import { useToast } from 'vue-toastification';

// Shadcn UI Components
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Badge } from '@/components/ui/badge';

// Icons
import { Copy, Loader2 } from 'lucide-vue-next';

const toast = useToast();

interface PresDecData {
    id: number | null;
    pd_number: string;
    date: string;
    citation: string;
    reference: string;
    url: string;
    pdf_availability?: boolean;
    subject: string;
    tenure: string;
    pdf_path: string;
}

interface ValidationErrors {
    pd_number?: string[];
    date?: string[];
    citation?: string[];
    reference?: string[];
    url?: string[];
    pdf_availability?: string[];
    subject?: string[];
    tenure?: string[];
    pdf_path?: string[];
}

const props = defineProps<{
    open: boolean;
    presdecData: PresDecData;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'saved'): void;
}>();

const processing = ref(false);
const errors = ref<ValidationErrors>({});
const duplicating = ref(false);
const duplicateOpen = ref(false);
const editingId = ref<number | null>(null);
const isDuplicateEdit = ref(false);

const formData = ref({
    pd_number: '',
    date: '',
    citation: '',
    reference: '',
    url: '',
    pdf_availability: false,
    subject: '',
    tenure: '',
    pdf_path: '',
});

// Watch for presdecData changes to populate form
watch(
    () => props.presdecData,
    (newData) => {
        if (newData && newData.id) {
            formData.value = {
                pd_number: newData.pd_number || '',
                date: newData.date ? new Date(newData.date).toISOString().split('T')[0] : '',
                citation: newData.citation || '',
                reference: newData.reference || '',
                url: newData.url || '',
                pdf_availability: newData.pdf_availability || false,
                subject: newData.subject || '',
                tenure: newData.tenure || '',
                pdf_path: newData.pdf_path || '',
            };
            editingId.value = newData.id;
            isDuplicateEdit.value = false;
        }
    },
    { immediate: true },
);

const updateCase = async () => {
    if (!props.presdecData || !props.presdecData.id) {
        toast.error('Error: No presidential decrees data selected.');
        return;
    }
    errors.value = {};
    processing.value = true;

    try {
        const response = await axios.post(
            `/api/v1/presidential/${editingId.value ?? props.presdecData.id}`,
            {
                pd_number: formData.value.pd_number,
                date: formData.value.date,
                citation: formData.value.citation,
                reference: formData.value.reference,
                url: formData.value.url,
                pdf_availability: formData.value.pdf_availability,
                subject: formData.value.subject,
                tenure: formData.value.tenure,
                pdf_path: formData.value.pdf_path,
            },
            {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                },
            },
        );

        if (response.data.success) {
            toast.success('Presidential Decrees updated successfully!');
            emit('update:open', false);
            emit('saved');
        } else {
            throw new Error(response.data.message || 'Failed to update presidential decree');
        }
    } catch (error: any) {
        console.error('Error updating presidential decrees:', error);

        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
            toast.error('Please check the form for errors');
        } else {
            const errorMessage = error.response?.data?.message || error.message || 'Failed to update presidential decrees';
            toast.error(errorMessage);
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

const duplicateAsNew = async () => {
    errors.value = {};
    duplicating.value = true;

    try {
        const response = await axios.post(
            '/api/v1/presidential',
            {
                pd_number: formData.value.pd_number,
                date: formData.value.date,
                citation: formData.value.citation,
                reference: formData.value.reference,
                url: formData.value.url,
                pdf_availability: formData.value.pdf_availability,
                subject: formData.value.subject,
                tenure: formData.value.tenure,
                pdf_path: formData.value.pdf_path,
            },
            {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                },
            },
        );

        if (response.data.success) {
            const created = response.data.data;
            toast.success('Record duplicated — now editing the new record!');
            duplicateOpen.value = false;
            formData.value = {
                pd_number: created.pd_number || '',
                date: created.date ? new Date(created.date).toISOString().split('T')[0] : '',
                citation: created.citation || '',
                reference: created.reference || '',
                url: created.url || '',
                pdf_availability: created.pdf_availability || false,
                subject: created.subject || '',
                tenure: created.tenure || '',
                pdf_path: created.pdf_path || '',
            };
            editingId.value = created.id ?? null;
            isDuplicateEdit.value = true;
            emit('saved');
        } else {
            throw new Error(response.data.message || 'Failed to duplicate record');
        }
    } catch (error: any) {
        console.error('Error duplicating record:', error);

        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
            toast.error('Please check the form for errors');
        } else {
            const errorMessage = error.response?.data?.message || error.message || 'Failed to duplicate record';
            toast.error(errorMessage);
        }
    } finally {
        duplicating.value = false;
    }
};
</script>

<template>
    <Dialog :open="open" @update:open="closeDialog">
        <DialogContent class="max-h-[90vh] max-w-2xl overflow-y-auto">
            <DialogHeader>
                <div class="flex items-start justify-between gap-2 pr-6">
                    <DialogTitle>
                        {{ isDuplicateEdit ? 'Editing Duplicated Record' : 'Edit Presidential Decrees Information' }}
                    </DialogTitle>
                    <Badge v-if="isDuplicateEdit" variant="outline" class="shrink-0 border-primary/50 text-primary"> Duplicate </Badge>
                </div>
                <DialogDescription>
                    <template v-if="isDuplicateEdit">
                        You are editing the newly created duplicate
                        <span v-if="formData.pd_number" class="font-medium text-foreground">(P.D. No. {{ formData.pd_number }})</span>. Saving changes
                        updates this new record — the original is left untouched.
                    </template>
                    <template v-else> Update the presidential decrees details below. </template>
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4 py-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="pd_number">PD Number</Label>
                        <Input
                            id="pd_number"
                            v-model="formData.pd_number"
                            :class="{ 'border-destructive': errors.pd_number }"
                            :disabled="processing"
                        />
                        <p v-if="errors.pd_number" class="text-xs text-destructive">{{ errors.pd_number[0] }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="date">Date</Label>
                        <Input id="date" type="date" v-model="formData.date" :class="{ 'border-destructive': errors.date }" :disabled="processing" />
                        <p v-if="errors.date" class="text-xs text-destructive">{{ errors.date[0] }}</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="citation">Description</Label>
                    <Textarea
                        id="citation"
                        v-model="formData.citation"
                        :class="{ 'border-destructive': errors.citation }"
                        rows="3"
                        :disabled="processing"
                    />
                    <p v-if="errors.citation" class="text-xs text-destructive">{{ errors.citation[0] }}</p>
                </div>

                <div class="space-y-2">
                    <Label for="reference">Reference</Label>
                    <Input id="reference" v-model="formData.reference" :disabled="processing" />
                </div>

                <div class="space-y-2">
                    <Label for="tenure">Tenure</Label>
                    <Input id="tenure" v-model="formData.tenure" placeholder="e.g., Justice Dela Cruz" :disabled="processing" />
                </div>

                <div class="space-y-2">
                    <Label for="url">URL</Label>
                    <Input id="url" v-model="formData.url" placeholder="https://..." :disabled="processing" />
                </div>

                <div class="space-y-2">
                    <Label for="subject">Other Keyword</Label>
                    <Textarea
                        id="subject"
                        v-model="formData.subject"
                        :class="{ 'border-destructive': errors.subject }"
                        rows="2"
                        placeholder="Enter the subject or topic of the case"
                        :disabled="processing"
                    />
                    <p v-if="errors.subject" class="text-xs text-destructive">{{ errors.subject[0] }}</p>
                </div>

                <!-- PDF Availability Checkbox -->
                <div class="flex items-start gap-4">
                    <div class="flex items-center space-x-2 pt-2">
                        <Checkbox id="pdf_availability" v-model:checked="formData.pdf_availability" :disabled="processing" />
                        <Label for="pdf_availability" class="cursor-pointer whitespace-nowrap"> PDF Available </Label>
                    </div>

                    <div class="flex-1">
                        <Input
                            id="pdf_path"
                            v-model="formData.pdf_path"
                            type="text"
                            placeholder="Enter PDF path or URL"
                            :class="{ 'border-destructive': errors.pdf_path }"
                            :disabled="processing || !formData.pdf_availability"
                            class="w-full"
                        />
                        <p v-if="errors.pdf_path" class="mt-1 text-xs text-destructive">{{ errors.pdf_path[0] }}</p>
                    </div>
                </div>
            </div>

            <DialogFooter>
                <Button variant="outline" @click="closeDialog" :disabled="processing || duplicating"> Cancel </Button>
                <Button variant="outline" @click="duplicateOpen = true" :disabled="processing || duplicating || isDuplicateEdit" class="gap-2">
                    <Copy class="h-4 w-4" />
                    Duplicate
                </Button>
                <Button @click="updateCase" :disabled="processing || duplicating">
                    <Loader2 v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                    {{ processing ? 'Updating...' : 'Save Changes' }}
                </Button>
            </DialogFooter>
        </DialogContent>
        <AlertDialog :open="duplicateOpen" @update:open="duplicateOpen = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Duplicate as new record?</AlertDialogTitle>
                    <AlertDialogDescription>
                        A new record will be created with the current details. Your edits will not be applied to the original record.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel :disabled="duplicating">Cancel</AlertDialogCancel>
                    <AlertDialogAction @click.prevent="duplicateAsNew" :disabled="duplicating">
                        <Loader2 v-if="duplicating" class="mr-2 h-4 w-4 animate-spin" />
                        {{ duplicating ? 'Duplicating...' : 'Duplicate' }}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </Dialog>
</template>
