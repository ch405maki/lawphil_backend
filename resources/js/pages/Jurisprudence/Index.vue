<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, reactive, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    cases: Object,
    filters: Object
});

const search = ref(props.filters?.search || '');
const importing = ref(false);
const showEditModal = ref(false);
const pdfFile = ref(null);
const processing = ref(false);
const errors = ref({});

const filterState = reactive({ 
    year: props.filters?.year || '', 
    sort: props.filters?.sort || 'latest',
    rows: props.filters?.rows || 10
});

const currentCase = reactive({ 
    id: null, 
    gr_number: '', 
    date: '', 
    citation: '', 
    ponente: '', 
    reference: '',
    url: '',
    pdf_availability: false,
    pdf_path: '' 
});

// Logic for generating numbered pagination
const pageNumbers = computed(() => {
    const total = props.cases.last_page;
    const current = props.cases.current_page;
    const delta = 2;
    const range = [];
    for (let i = Math.max(2, current - delta); i <= Math.min(total - 1, current + delta); i++) {
        range.push(i);
    }
    if (current - delta > 2) range.unshift('...');
    range.unshift(1);
    if (current + delta < total - 1) range.push('...');
    if (total > 1) range.push(total);
    return range;
});

const fetchData = (page = props.cases.current_page) => {
    router.get(route('jurisprudence.index'), { 
        search: search.value, 
        page: page,
        year: filterState.year,
        sort: filterState.sort,
        rows: filterState.rows
    }, {
        preserveState: true,
        replace: true,
        preserveScroll: true
    });
};

const importExcel = (e) => {
    const file = e.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('file', file);

    importing.value = true;
    router.post(route('jurisprudence.import'), formData, {
        onFinish: () => {
            importing.value = false;
            e.target.value = '';
        },
        forceFormData: true
    });
};

// Added filterState.rows to the watcher to trigger refresh when item count changes
watch([() => filterState.year, () => filterState.sort, () => filterState.rows], () => fetchData(1));

const openEdit = (item) => { 
    errors.value = {};
    Object.assign(currentCase, item); 
    if (item.date) {
        currentCase.date = new Date(item.date).toISOString().split('T')[0];
    }
    pdfFile.value = null; 
    showEditModal.value = true; 
};

const handlePdfChange = (e) => {
    pdfFile.value = e.target.files[0];
};

const updateCase = () => {
    errors.value = {};
    processing.value = true;

    const formData = new FormData();
    formData.append('_method', 'PUT'); 
    formData.append('gr_number', currentCase.gr_number || '');
    formData.append('date', currentCase.date || '');
    formData.append('citation', currentCase.citation || '');
    formData.append('ponente', currentCase.ponente || '');
    formData.append('reference', currentCase.reference || '');
    formData.append('url', currentCase.url || '');
    
    if (pdfFile.value) {
        formData.append('pdf_file', pdfFile.value);
    }

    router.post(route('jurisprudence.update', currentCase.id), formData, {
        forceFormData: true, 
        onSuccess: () => {
            showEditModal.value = false;
            pdfFile.value = null;
        },
        onError: (err) => {
            errors.value = err;
        },
        onFinish: () => {
            processing.value = false;
        },
        preserveScroll: true
    });
};

const deleteCase = (id) => {
    if (!confirm("Are you sure?")) return;
    router.delete(route('jurisprudence.destroy', id));
};

const formatDate = (d) => d ? new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A';
</script>

<template>
    <AppLayout title="Jurisprudence">
        <div class="min-h-screen bg-slate-50 text-slate-800 font-sans relative">
            
            <div v-if="importing" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm">
                <div class="bg-white p-6 rounded-2xl shadow-xl flex flex-col items-center gap-4">
                    <div class="w-12 h-12 border-4 border-[#5c1e99] border-t-transparent rounded-full animate-spin"></div>
                    <p class="text-sm font-bold text-slate-700">Processing Import... Please wait.</p>
                </div>
            </div>

            <div class="bg-white border-b border-slate-200 py-6 px-8 sticky top-0 z-10">
                <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Jurisprudence Bank</h1>
                        <p class="text-sm text-slate-500 font-medium">Legal Archives Management System</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="cursor-pointer px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50 transition shadow-sm">
                            Import Excel/CSV
                            <input type="file" @change="importExcel" class="hidden" accept=".xlsx,.xls,.csv" />
                        </label>
                        <button class="bg-[#5c1e99] hover:bg-[#4a187a] text-white px-5 py-2 rounded-lg text-sm font-semibold shadow-md transition-all active:scale-95">
                            + Add New Case
                        </button>
                    </div>
                </div>
            </div>

            <main class="max-w-7xl mx-auto px-8 py-8">
                
                <div class="bg-white p-4 rounded-t-xl border border-slate-200 flex flex-wrap items-center gap-4">
                    <div class="flex-1 min-w-[300px] relative">
                        <div class="absolute left-3 top-2.5 text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input v-model="search" @input="fetchData(1)" placeholder="Search G.R. No. or Case Title..." 
                            class="w-full bg-slate-50 border-slate-200 rounded-lg py-2 pl-10 text-sm focus:ring-2 focus:ring-[#5c1e99]/20 focus:border-[#5c1e99] outline-none transition-all" />
                    </div>
                    <div class="flex items-center gap-2">
                        <select v-model="filterState.sort" class="border-slate-200 bg-slate-50 rounded-lg py-2 text-sm p-2 outline-none focus:border-[#5c1e99]">
                            <option value="latest">Newest First</option>
                            <option value="oldest">Oldest First</option>
                            <option value="az">A-Z (Title)</option>
                            <option value="za">Z-A (Title)</option>
                        </select>
                        <input v-model="filterState.year" type="number" placeholder="Year" class="w-24 border-slate-200 bg-slate-50 rounded-lg py-2 text-sm p-2 focus:border-[#5c1e99]" />
                        <button @click="Object.assign(filterState, {year: '', sort: 'latest', rows: 10})" class="text-xs font-bold text-[#5c1e99] hover:text-[#4a187a] px-2">Reset</button>
                    </div>
                </div>

                <div class="bg-white border-x border-b border-slate-200 shadow-sm overflow-hidden rounded-b-xl">
                    <table class="w-full text-left text-sm table-fixed">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold text-[11px] uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-4 w-[15%]">G.R. & Date</th>
                                <th class="px-6 py-4 w-[40%]">Case Title</th>
                                <th class="px-6 py-4 w-[15%] text-center">Reference</th>
                                <th class="px-6 py-4 w-[10%] text-center">PDF</th>
                                <th class="px-6 py-4 w-[20%] text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="item in cases.data" :key="item.id" class="hover:bg-[#5c1e99]/5 transition-colors group">
                                <td class="px-6 py-5 align-top">
                                    <div class="font-bold text-slate-900 whitespace-nowrap">{{ item.gr_number }}</div>
                                    <div class="text-[11px] text-slate-400 mt-1 font-medium">{{ formatDate(item.date) }}</div>
                                </td>
                                <td class="px-6 py-5 align-top">
                                    <div class="line-clamp-2 font-semibold text-slate-700 leading-relaxed group-hover:text-[#5c1e99] transition-colors">
                                        {{ item.citation }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-1.5 uppercase tracking-tighter italic">
                                        Ponente: {{ item.ponente || 'N/A' }} • Vol: {{ item.reference }}
                                    </div>
                                </td>
                                <td class="px-6 py-5 align-top text-center">
                                    <a v-if="item.url" :href="item.url" target="_blank" class="text-[#5c1e99] hover:underline text-xs font-bold inline-flex items-center gap-1">
                                        Link ↗
                                    </a>
                                    <span v-else class="text-slate-300">—</span>
                                </td>
                                <td class="px-6 py-5 align-top text-center">
                                    <a v-if="item.pdf_availability" :href="item.pdf_path" target="_blank" 
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all shadow-sm">
                                        <span class="text-xs font-bold">PDF</span>
                                    </a>
                                    <span v-else class="text-slate-200">—</span>
                                </td>
                                <td class="px-6 py-5 align-top text-right">
                                    <div class="flex justify-end gap-4">
                                        <button @click="openEdit(item)" class="text-[#5c1e99] hover:text-[#4a187a] font-bold">Edit</button>
                                        <button @click="deleteCase(item.id)" class="text-slate-300 hover:text-red-500 transition-colors">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="text-xs text-slate-500 font-medium">
                                Showing {{ cases.from }} to {{ cases.to }} of {{ cases.total }} entries
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-bold text-slate-400 uppercase">Show</span>
                                <select v-model="filterState.rows" class="bg-white border-slate-200 rounded-md text-[11px] font-bold py-1 px-2 focus:ring-1 focus:ring-[#5c1e99] outline-none transition-all">
                                    <option :value="10">10</option>
                                    <option :value="25">25</option>
                                    <option :value="50">50</option>
                                    <option :value="100">100</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center gap-1">
                            <button @click="fetchData(cases.current_page - 1)" :disabled="cases.current_page === 1"
                                class="w-8 h-8 flex items-center justify-center bg-white border border-slate-200 rounded-lg text-slate-600 disabled:opacity-30 hover:bg-slate-50 transition shadow-sm mr-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                            </button>

                            <template v-for="(page, index) in pageNumbers" :key="index">
                                <button v-if="page !== '...'" 
                                    @click="fetchData(page)" 
                                    :class="[
                                        'w-8 h-8 rounded-lg text-xs font-bold transition-all',
                                        cases.current_page === page 
                                        ? 'bg-[#5c1e99] text-white shadow-md' 
                                        : 'bg-white border border-slate-200 text-slate-600 hover:border-[#5c1e99] hover:text-[#5c1e99]'
                                    ]">
                                    {{ page }}
                                </button>
                                <span v-else class="px-2 text-slate-400 text-xs font-bold">...</span>
                            </template>

                            <button @click="fetchData(cases.current_page + 1)" :disabled="cases.current_page === cases.last_page"
                                class="w-8 h-8 flex items-center justify-center bg-white border border-slate-200 rounded-lg text-slate-600 disabled:opacity-30 hover:bg-slate-50 transition shadow-sm ml-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <div v-if="showEditModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-md flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] flex flex-col overflow-hidden">
                <div class="px-8 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50 flex-shrink-0">
                    <h3 class="text-lg font-bold text-slate-800">Edit Case Information</h3>
                    <button @click="showEditModal = false" :disabled="processing" class="text-slate-400 hover:text-slate-600 text-xl">✕</button>
                </div>
                
                <div class="p-8 space-y-4 overflow-y-auto">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">G.R. Number</label>
                            <input v-model="currentCase.gr_number" :class="{'border-red-500': errors.gr_number}" class="w-full border-slate-200 rounded-lg text-sm p-2 focus:border-[#5c1e99] outline-none" />
                            <p v-if="errors.gr_number" class="text-red-500 text-[10px] mt-1">{{ errors.gr_number }}</p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Date</label>
                            <input type="date" v-model="currentCase.date" :class="{'border-red-500': errors.date}" class="w-full border-slate-200 rounded-lg text-sm p-2 focus:border-[#5c1e99] outline-none" />
                            <p v-if="errors.date" class="text-red-500 text-[10px] mt-1">{{ errors.date }}</p>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Case Citation / Title</label>
                        <textarea v-model="currentCase.citation" :class="{'border-red-500': errors.citation}" class="w-full border-slate-200 rounded-lg text-sm p-2 focus:border-[#5c1e99] outline-none" rows="3"></textarea>
                        <p v-if="errors.citation" class="text-red-500 text-[10px] mt-1">{{ errors.citation }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Ponente</label>
                            <input v-model="currentCase.ponente" class="w-full border-slate-200 rounded-lg text-sm p-2 focus:border-[#5c1e99] outline-none" />
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Volume</label>
                            <input v-model="currentCase.reference" class="w-full border-slate-200 rounded-lg text-sm p-2 focus:border-[#5c1e99] outline-none" />
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Reference URL</label>
                        <input v-model="currentCase.url" class="w-full border-slate-200 rounded-lg text-sm p-2 focus:border-[#5c1e99] outline-none" />
                    </div>

                    <div class="mt-4 p-4 rounded-xl bg-[#5c1e99]/5 border border-[#5c1e99]/10">
                        <label class="text-[10px] font-bold text-[#5c1e99] uppercase tracking-widest block mb-2">Upload PDF Document</label>
                        <input type="file" @change="handlePdfChange" accept=".pdf" class="text-xs file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-[#5c1e99] file:text-white hover:file:bg-[#4a187a]" />
                        <p v-if="errors.pdf_file" class="text-red-500 text-[10px] mt-1">{{ errors.pdf_file }}</p>
                    </div>

                    <div class="flex gap-3 pt-4 sticky bottom-0 bg-white pb-2 border-t border-slate-50 mt-4">
                        <button @click="updateCase" :disabled="processing" class="flex-1 bg-[#5c1e99] text-white py-3 rounded-xl text-sm font-bold shadow-lg hover:bg-[#4a187a] transition-all flex items-center justify-center gap-2">
                            <span v-if="processing" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            {{ processing ? 'Updating...' : 'Save Changes' }}
                        </button>
                        <button @click="showEditModal = false" :disabled="processing" class="px-6 py-3 text-slate-500 text-sm font-semibold hover:bg-slate-50 rounded-xl transition">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>