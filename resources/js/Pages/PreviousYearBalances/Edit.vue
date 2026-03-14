<script setup>
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    balance: Object,
    students: Array,
    academicYears: Array,
});

const form = useForm({
    student_enrollment_id: props.balance.student_enrollment_id || '',
    from_academic_year_id: props.balance.from_academic_year_id || '',
    to_academic_year_id: props.balance.to_academic_year_id || '',
    original_outstanding: props.balance.original_outstanding || '',
    breakup: props.balance.breakup || {},
    recovered_amount: props.balance.recovered_amount || '',
    remaining_amount: props.balance.remaining_amount || '',
    carry_forward_date: props.balance.carry_forward_date || '',
    status: props.balance.status || 'unpaid',
    notes: props.balance.notes || '',
});

const submit = () => {
    form.put(route('previous-year-balances.update', props.balance.id));
};
</script>

<template>
    <Head title="Edit Previous Year Balance" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">Edit Previous Year Balance</h2>

                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Amount Details -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Original Outstanding (Rs.) *</label>
                                    <input v-model.number="form.original_outstanding" type="number" step="0.01" required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Recovered Amount (Rs.)</label>
                                    <input v-model.number="form.recovered_amount" type="number" step="0.01"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Remaining Amount (Rs.) *</label>
                                    <input v-model.number="form.remaining_amount" type="number" step="0.01" required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                </div>
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                                <select v-model="form.status" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="unpaid">Unpaid</option>
                                    <option value="partial">Partial</option>
                                    <option value="cleared">Cleared</option>
                                </select>
                            </div>

                            <!-- Carry Forward Date -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Carry Forward Date *</label>
                                <input v-model="form.carry_forward_date" type="date" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>

                            <!-- Notes -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                <textarea v-model="form.notes" rows="3"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                            </div>

                            <!-- Actions -->
                            <div class="flex justify-end gap-4 pt-4">
                                <Link :href="route('previous-year-balances.index')"
                                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-colors">
                                    Cancel
                                </Link>
                                <button type="submit" :disabled="form.processing"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors disabled:opacity-50">
                                    Update Balance
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
