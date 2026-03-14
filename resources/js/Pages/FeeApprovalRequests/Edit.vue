<script setup>
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    request: Object,
    students: Array,
    approvers: Array,
});

const form = useForm({
    request_type: props.request.request_type || 'fee_concession',
    student_enrollment_id: props.request.student_enrollment_id || '',
    voucher_id: props.request.voucher_id || '',
    requested_amount: props.request.requested_amount || '',
    requested_percent: props.request.requested_percent || '',
    current_amount: props.request.current_amount || '',
    reason: props.request.reason || '',
    supporting_notes: props.request.supporting_notes || '',
    urgency: props.request.urgency || 'medium',
    status: props.request.status || 'pending',
    reviewer_remarks: props.request.reviewer_remarks || '',
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

const statuses = [
    { value: 'pending', label: 'Pending' },
    { value: 'approved', label: 'Approved' },
    { value: 'rejected', label: 'Rejected' },
    { value: 'processed', label: 'Processed' },
];

const submit = () => {
    form.put(route('fee-approval-requests.update', props.request.id));
};
</script>

<template>
    <Head title="Edit Fee Approval Request" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">Edit Fee Approval Request</h2>

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
                            </div>

                            <!-- Amount Details -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Current Amount (Rs.) *</label>
                                    <input v-model.number="form.current_amount" type="number" step="0.01" required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Requested Amount (Rs.) *</label>
                                    <input v-model.number="form.requested_amount" type="number" step="0.01" required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Requested Percent (%)</label>
                                    <input v-model.number="form.requested_percent" type="number" step="0.01" min="0" max="100"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
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
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select v-model="form.status"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option v-for="status in statuses" :key="status.value" :value="status.value">
                                        {{ status.label }}
                                    </option>
                                </select>
                            </div>

                            <!-- Reason -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Reason *</label>
                                <textarea v-model="form.reason" rows="3" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                            </div>

                            <!-- Supporting Notes -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Supporting Notes</label>
                                <textarea v-model="form.supporting_notes" rows="2"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                            </div>

                            <!-- Reviewer Remarks -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Reviewer Remarks</label>
                                <textarea v-model="form.reviewer_remarks" rows="2"
                                    placeholder="Reviewer's comments..."
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
                                    Update Request
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
