<script setup>
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const search = ref('');
const loading = ref(false);

const columns = [
    { key: 'DT_RowIndex', label: 'S.No', width: '60px' },
    { key: 'fee_type', label: 'Fee Type', width: '120px' },
    { key: 'class_name', label: 'Class', width: '100px' },
    { key: 'old_amount', label: 'Old Amount', width: '100px' },
    { key: 'new_amount', label: 'New Amount', width: '100px' },
    { key: 'change', label: 'Change', width: '100px' },
    { key: 'changed_at', label: 'Changed At', width: '140px' },
    { key: 'action', label: 'Action', width: '100px' },
];

const logs = ref([]);
const totalRecords = ref(0);
const currentPage = ref(1);
const perPage = ref(10);

const fetchLogs = async () => {
    loading.value = true;
    try {
        const response = await fetch(`/fee-structure-change-logs?draw=1&start=${(currentPage - 1) * perPage.value}&length=${perPage.value}&search[value]=${search.value}`);
        const data = await response.json();
        logs.value = data.data;
        totalRecords.value = data.recordsFiltered;
    } catch (error) {
        console.error('Error fetching logs:', error);
    } finally {
        loading.value = false;
    }
};

const viewLog = (data) => {
    router.get(`/fee-structure-change-logs/${data.id}`);
};

const clearFilters = () => {
    search.value = '';
    fetchLogs();
};

fetchLogs();
</script>

<template>
    <Head title="Fee Structure Change Logs" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h2 class="text-2xl font-bold text-gray-800">Fee Structure Change Logs</h2>
                        <p class="mt-1 text-sm text-gray-600">Audit trail of all fee structure changes</p>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                <input v-model="search" @keyup.enter="fetchLogs" type="text"
                                    placeholder="Search by reason or fee type..."
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                            <div class="flex items-end">
                                <button @click="clearFilters"
                                    class="w-full px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-colors">
                                    Clear Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="loading" class="text-center py-8">
                            <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4" />
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                            </svg>
                            <p class="mt-2 text-gray-600">Loading change logs...</p>
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th v-for="column in columns" :key="column.key" :width="column.width"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ column.label }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="log in logs" :key="log.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ log.DT_RowIndex }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ log.fee_type }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ log.class_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            Rs. {{ log.old_amount }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            Rs. {{ log.new_amount }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span v-html="log.change"></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ log.changed_at }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <div v-html="log.action"></div>
                                        </td>
                                    </tr>
                                    <tr v-if="logs.length === 0">
                                        <td :colspan="columns.length" class="px-6 py-8 text-center text-gray-500">
                                            No change logs found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="logs.length > 0" class="mt-4 flex justify-between items-center">
                            <div class="text-sm text-gray-700">
                                Showing <span class="font-medium">{{ (currentPage - 1) * perPage + 1 }}</span> to
                                <span class="font-medium">{{ Math.min(currentPage * perPage, totalRecords) }}</span> of
                                <span class="font-medium">{{ totalRecords }}</span> results
                            </div>
                            <div class="flex gap-2">
                                <button @click="currentPage--" :disabled="currentPage === 1"
                                    class="px-4 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50">
                                    Previous
                                </button>
                                <button @click="currentPage++"
                                    :disabled="currentPage * perPage >= totalRecords"
                                    class="px-4 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50">
                                    Next
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
