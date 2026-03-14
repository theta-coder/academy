<script setup>
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    log: Object,
    academicYears: Array,
    branches: Array,
    classes: Array,
    feeTypes: Array,
});

const form = useForm({
    old_amount: props.log.old_amount || '',
    new_amount: props.log.new_amount || '',
    old_due_day: props.log.old_due_day || '',
    new_due_day: props.log.new_due_day || '',
    change_reason: props.log.change_reason || '',
    effective_from: props.log.effective_from || '',
    affects_existing_vouchers: props.log.affects_existing_vouchers || false,
});

const submit = () => {
    form.put(route('fee-structure-change-logs.update', props.log.id));
};
</script>

<template>
    <Head title="Edit Change Log" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">Edit Change Log</h2>

                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Amount Fields -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Old Amount (Rs.)</label>
                                    <input v-model.number="form.old_amount" type="number" step="0.01" readonly
                                        class="w-full rounded-md border-gray-300 bg-gray-100 shadow-sm" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">New Amount (Rs.) *</label>
                                    <input v-model.number="form.new_amount" type="number" step="0.01" required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                    <span v-if="form.errors.new_amount" class="text-red-500 text-sm">
                                        {{ form.errors.new_amount }}
                                    </span>
                                </div>
                            </div>

                            <!-- Due Day Fields -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Old Due Day</label>
                                    <input v-model="form.old_due_day" type="number" min="1" max="31" readonly
                                        class="w-full rounded-md border-gray-300 bg-gray-100 shadow-sm" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">New Due Day</label>
                                    <input v-model="form.new_due_day" type="number" min="1" max="31"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                </div>
                            </div>

                            <!-- Change Reason -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Change Reason *</label>
                                <textarea v-model="form.change_reason" rows="3" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                                <span v-if="form.errors.change_reason" class="text-red-500 text-sm">
                                    {{ form.errors.change_reason }}
                                </span>
                            </div>

                            <!-- Effective From -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Effective From *</label>
                                <input v-model="form.effective_from" type="date" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                <span v-if="form.errors.effective_from" class="text-red-500 text-sm">
                                    {{ form.errors.effective_from }}
                                </span>
                            </div>

                            <!-- Affects Existing Vouchers -->
                            <div class="flex items-center">
                                <input v-model="form.affects_existing_vouchers" type="checkbox"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
                                <label class="ml-2 block text-sm text-gray-700">
                                    Affects Existing Vouchers
                                </label>
                            </div>

                            <!-- Actions -->
                            <div class="flex justify-end gap-4 pt-4">
                                <Link :href="route('fee-structure-change-logs.index')"
                                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-colors">
                                    Cancel
                                </Link>
                                <button type="submit" :disabled="form.processing"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors disabled:opacity-50">
                                    Update Log
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
