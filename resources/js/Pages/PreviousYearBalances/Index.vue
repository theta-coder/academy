<script setup>
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const search = ref('');
const status = ref('');
const loading = ref(false);

const columns = [
    { key: 'DT_RowIndex', label: 'S.No', width: '60px' },
    { key: 'student_name', label: 'Student', width: '150px' },
    { key: 'from_year', label: 'From Year', width: '100px' },
    { key: 'to_year', label: 'To Year', width: '100px' },
    { key: 'original_outstanding', label: 'Original', width: '100px' },
    { key: 'remaining_amount', label: 'Remaining', width: '100px' },
    { key: 'status', label: 'Status', width: '100px' },
    { key: 'action', label: 'Action', width: '150px' },
];

const balances = ref([]);
const totalRecords = ref(0);
const currentPage = ref(1);
const perPage = ref(10);

const fetchBalances = async () => {
    loading.value = true;
    try {
        const response = await fetch(`/previous-year-balances?draw=1&start=${(currentPage - 1) * perPage.value}&length=${perPage.value}&search[value]=${search.value}&status=${status.value}`);
        const data = await response.json();
        balances.value = data.data;
        totalRecords.value = data.recordsFiltered;
    } catch (error) {
        console.error('Error fetching balances:', error);
    } finally {
        loading.value = false;
    }
};

const deleteBalance = (id) => {
    if (confirm('Are you sure you want to delete this balance record?')) {
        router.delete(`/previous-year-balances/${id}`, {
            onSuccess: () => fetchBalances(),
        });
    }
};

const editBalance = (data) => {
    router.get(`/previous-year-balances/${data.id}/edit`);
};

const viewBalance = (data) => {
    router.get(`/previous-year-balances/${data.id}`);
};

const clearFilters = () => {
    search.value = '';
    status.value = '';
    fetchBalances();
};

fetchBalances();
</script>

<template>
    <Head title="Previous Year Balances" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h2 class="text-2xl font-bold text-gray-800">Previous Year Balances</h2>
                            <Link :href="route('previous-year-balances.create')"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Carry Forward Balance
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                <input v-model="search" @keyup.enter="fetchBalances" type="text"
                                    placeholder="Search by student or notes..."
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select v-model="status" @change="fetchBalances"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">All Statuses</option>
                                    <option value="unpaid">Unpaid</option>
                                    <option value="partial">Partial</option>
                                    <option value="cleared">Cleared</option>
                                </select>
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
                            <p class="mt-2 text-gray-600">Loading balances...</p>
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
                                    <tr v-for="balance in balances" :key="balance.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ balance.DT_RowIndex }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ balance.student_name }}</div>
                                            <div class="text-sm text-gray-500">{{ balance.admission_no }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ balance.from_year }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ balance.to_year }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            Rs. {{ balance.original_outstanding }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            Rs. {{ balance.remaining_amount }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span v-html="balance.status"></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <div v-html="balance.action"></div>
                                        </td>
                                    </tr>
                                    <tr v-if="balances.length === 0">
                                        <td :colspan="columns.length" class="px-6 py-8 text-center text-gray-500">
                                            No previous year balances found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="balances.length > 0" class="mt-4 flex justify-between items-center">
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
