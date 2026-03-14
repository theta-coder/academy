<script setup>
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    academicYears: Array,
    branches: Array,
    classes: Array,
    feeTypes: Array,
    feeStructures: Array,
});

const form = useForm({
    fee_structure_id: '',
    branch_id: '',
    class_id: '',
    fee_type_id: '',
    academic_year_id: '',
    old_amount: '',
    new_amount: '',
    old_due_day: '',
    new_due_day: '',
    change_reason: '',
    effective_from: '',
    affects_existing_vouchers: false,
});

const selectedStructure = ref(null);

const fetchStructureDetails = async () => {
    if (!form.fee_structure_id) return;
    try {
        const response = await fetch(`/api/fee-structure-change-logs/structure-details/${form.fee_structure_id}`);
        selectedStructure.value = await response.json();
        form.old_amount = selectedStructure.value.amount;
        form.old_due_day = selectedStructure.value.due_day;
        form.branch_id = selectedStructure.value.branch_id;
        form.class_id = selectedStructure.value.class_id;
        form.fee_type_id = selectedStructure.value.fee_type_id;
        form.academic_year_id = selectedStructure.value.academic_year_id;
    } catch (error) {
        console.error('Error fetching structure details:', error);
    }
};

const submit = () => {
    form.post(route('fee-structure-change-logs.store'));
};
</script>

<template>
    <Head title="Log Fee Structure Change" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">Log Fee Structure Change</h2>

                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Select Fee Structure -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Select Fee Structure *</label>
                                <select v-model="form.fee_structure_id" @change="fetchStructureDetails" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select Structure</option>
                                    <option v-for="structure in feeStructures" :key="structure.id" :value="structure.id">
                                        {{ structure.fee_type?.fee_name }} - {{ structure.class?.class_name }} - {{ structure.branch?.branch_name }}
                                    </option>
                                </select>
                                <span v-if="form.errors.fee_structure_id" class="text-red-500 text-sm">
                                    {{ form.errors.fee_structure_id }}
                                </span>
                            </div>

                            <!-- Current Details (Read-only) -->
                            <div v-if="selectedStructure" class="bg-blue-50 p-4 rounded-lg">
                                <h3 class="text-sm font-medium text-blue-800 mb-2">Current Structure Details</h3>
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-blue-600">Current Amount:</span>
                                        <span class="ml-2 font-medium">Rs. {{ selectedStructure.amount }}</span>
                                    </div>
                                    <div>
                                        <span class="text-blue-600">Due Day:</span>
                                        <span class="ml-2 font-medium">Day {{ selectedStructure.due_day || '-' }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- New Amount -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">New Amount (Rs.) *</label>
                                <input v-model.number="form.new_amount" type="number" step="0.01" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                <span v-if="form.errors.new_amount" class="text-red-500 text-sm">
                                    {{ form.errors.new_amount }}
                                </span>
                            </div>

                            <!-- Due Day Change -->
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
                                    placeholder="Explain why this change is being made..."
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
                                    Log Change
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
