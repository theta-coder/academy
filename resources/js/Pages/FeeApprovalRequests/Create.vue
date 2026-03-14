<script setup>
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    students: Array,
    approvers: Array,
});

const form = useForm({
    request_type: 'fee_concession',
    student_enrollment_id: '',
    voucher_id: '',
    requested_amount: '',
    requested_percent: '',
    current_amount: '',
    reason: '',
    supporting_notes: '',
    urgency: 'medium',
    status: 'pending',
    reviewer_remarks: '',
});

const requestTypes = [
    { value: 'fee_concession', label: 'Fee Concession' },
    { value: 'fee_waiver', label: 'Fee Waiver' },
    { value: 'fine_waiver', label: 'Fine Waiver' },
    { value: 'installment_plan', label: 'Installment Plan' },
    { value: 'fee_refund', label: 'Fee Refund' },
];

const urgencies = [
    { value: 'low', label: 'Low' },
    { value: 'medium', label: 'Medium' },
    { value: 'high', label: 'High' },
];

const submit = () => {
    form.post(route('fee-approval-requests.store'));
};
</script>

<template>
    <Head title="Create Fee Approval Request" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">Create Fee Approval Request</h2>

                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Request Type -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Request Type *</label>
                                <select v-model="form.request_type" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option v-for="type in requestTypes" :key="type.value" :value="type.value">
                                        {{ type.label }}
                                    </option>
                                </select>
                                <span v-if="form.errors.request_type" class="text-red-500 text-sm">
                                    {{ form.errors.request_type }}
                                </span>
                            </div>

                            <!-- Student -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Student *</label>
                                <select v-model="form.student_enrollment_id" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select Student</option>
                                    <option v-for="student in students" :key="student.id" :value="student.id">
                                        {{ student.student_name }} ({{ student.admission_no }})
                                    </option>
                                </select>
                                <span v-if="form.errors.student_enrollment_id" class="text-red-500 text-sm">
                                    {{ form.errors.student_enrollment_id }}
                                </span>
                            </div>

                            <!-- Amount Details -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Current Amount (Rs.) *</label>
                                    <input v-model.number="form.current_amount" type="number" step="0.01" required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                    <span v-if="form.errors.current_amount" class="text-red-500 text-sm">
                                        {{ form.errors.current_amount }}
                                    </span>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Requested Amount (Rs.) *</label>
                                    <input v-model.number="form.requested_amount" type="number" step="0.01" required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                    <span v-if="form.errors.requested_amount" class="text-red-500 text-sm">
                                        {{ form.errors.requested_amount }}
                                    </span>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Requested Percent (%)</label>
                                    <input v-model.number="form.requested_percent" type="number" step="0.01" min="0" max="100"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                    <span v-if="form.errors.requested_percent" class="text-red-500 text-sm">
                                        {{ form.errors.requested_percent }}
                                    </span>
                                </div>
                            </div>

                            <!-- Urgency -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Urgency *</label>
                                <select v-model="form.urgency" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option v-for="urgency in urgencies" :key="urgency.value" :value="urgency.value">
                                        {{ urgency.label }}
                                    </option>
                                </select>
                                <span v-if="form.errors.urgency" class="text-red-500 text-sm">
                                    {{ form.errors.urgency }}
                                </span>
                            </div>

                            <!-- Reason -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Reason *</label>
                                <textarea v-model="form.reason" rows="3" required
                                    placeholder="Explain why this approval is needed..."
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                                <span v-if="form.errors.reason" class="text-red-500 text-sm">
                                    {{ form.errors.reason }}
                                </span>
                            </div>

                            <!-- Supporting Notes -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Supporting Notes</label>
                                <textarea v-model="form.supporting_notes" rows="2"
                                    placeholder="Any additional information..."
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                            </div>

                            <!-- Actions -->
                            <div class="flex justify-end gap-4 pt-4">
                                <Link :href="route('fee-approval-requests.index')"
                                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-colors">
                                    Cancel
                                </Link>
                                <button type="submit" :disabled="form.processing"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors disabled:opacity-50">
                                    Submit Request
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
