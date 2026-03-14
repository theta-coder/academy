<script setup>
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    request: Object,
});

const getStatusClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800',
        processed: 'bg-blue-100 text-blue-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const getUrgencyClass = (urgency) => {
    const classes = {
        low: 'bg-green-100 text-green-800',
        medium: 'bg-yellow-100 text-yellow-800',
        high: 'bg-red-100 text-red-800',
    };
    return classes[urgency] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Fee Approval Request Details" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold text-gray-800">Fee Approval Request Details</h2>
                            <Link :href="route('fee-approval-requests.index')"
                                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-colors">
                                Back to List
                            </Link>
                        </div>

                        <!-- Status Badges -->
                        <div class="flex gap-4 mb-6">
                            <span :class="['px-3 py-1 text-sm font-medium rounded-full', getStatusClass(request.status)]">
                                {{ request.status }}
                            </span>
                            <span :class="['px-3 py-1 text-sm font-medium rounded-full', getUrgencyClass(request.urgency)]">
                                {{ request.urgency }} Urgency
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Request Info -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-800 mb-3">Request Information</h3>
                                <div class="space-y-2">
                                    <div>
                                        <span class="text-sm text-gray-500">Request Type:</span>
                                        <p class="font-medium capitalize">{{ request.request_type?.replace('_', ' ') || '-' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Voucher No:</span>
                                        <p class="font-medium">{{ request.voucher?.voucher_no || '-' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Amount Details -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-800 mb-3">Amount Details</h3>
                                <div class="space-y-2">
                                    <div>
                                        <span class="text-sm text-gray-500">Current Amount:</span>
                                        <p class="font-medium">Rs. {{ request.current_amount || '0.00' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Requested Amount:</span>
                                        <p class="font-medium">Rs. {{ request.requested_amount || '0.00' }}</p>
                                    </div>
                                    <div v-if="request.requested_percent">
                                        <span class="text-sm text-gray-500">Requested Percent:</span>
                                        <p class="font-medium">{{ request.requested_percent }}%</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Student Info -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-800 mb-3">Student Information</h3>
                                <div class="space-y-2">
                                    <div>
                                        <span class="text-sm text-gray-500">Student Name:</span>
                                        <p class="font-medium">{{ request.student_enrollment?.student?.student_name || '-' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Admission No:</span>
                                        <p class="font-medium">{{ request.student_enrollment?.student?.admission_no || '-' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Timeline -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-800 mb-3">Timeline</h3>
                                <div class="space-y-2">
                                    <div>
                                        <span class="text-sm text-gray-500">Requested At:</span>
                                        <p class="font-medium">{{ request.requested_at || '-' }}</p>
                                    </div>
                                    <div v-if="request.reviewed_at">
                                        <span class="text-sm text-gray-500">Reviewed At:</span>
                                        <p class="font-medium">{{ request.reviewed_at }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Requested By -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-800 mb-3">Requested By</h3>
                                <p class="font-medium">{{ request.requested_by?.name || '-' }}</p>
                            </div>

                            <!-- Reviewed By -->
                            <div v-if="request.reviewed_by" class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-800 mb-3">Reviewed By</h3>
                                <p class="font-medium">{{ request.reviewed_by?.name || '-' }}</p>
                            </div>

                            <!-- Reason -->
                            <div class="bg-gray-50 p-4 rounded-lg md:col-span-2">
                                <h3 class="text-lg font-semibold text-gray-800 mb-3">Reason</h3>
                                <p class="font-medium">{{ request.reason || 'No reason provided' }}</p>
                            </div>

                            <!-- Supporting Notes -->
                            <div class="bg-gray-50 p-4 rounded-lg md:col-span-2">
                                <h3 class="text-lg font-semibold text-gray-800 mb-3">Supporting Notes</h3>
                                <p class="font-medium">{{ request.supporting_notes || 'No supporting notes' }}</p>
                            </div>

                            <!-- Reviewer Remarks -->
                            <div v-if="request.reviewer_remarks" class="bg-gray-50 p-4 rounded-lg md:col-span-2">
                                <h3 class="text-lg font-semibold text-gray-800 mb-3">Reviewer Remarks</h3>
                                <p class="font-medium">{{ request.reviewer_remarks }}</p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end gap-4 mt-6 pt-6 border-t">
                            <Link v-if="request.status === 'pending'"
                                :href="route('fee-approval-requests.edit', request.id)"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
                                Edit Request
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
