<script setup>
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    reminder: Object,
});
</script>

<template>
    <Head title="Fee Reminder Details" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold text-gray-800">Fee Reminder Details</h2>
                            <Link :href="route('fee-reminders.index')"
                                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-colors">
                                Back to List
                            </Link>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Student Info -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-800 mb-3">Student Information</h3>
                                <div class="space-y-2">
                                    <div>
                                        <span class="text-sm text-gray-500">Student Name:</span>
                                        <p class="font-medium">{{ reminder.student_enrollment?.student?.student_name || '-' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Admission No:</span>
                                        <p class="font-medium">{{ reminder.student_enrollment?.student?.admission_no || '-' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Reminder Info -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-800 mb-3">Reminder Information</h3>
                                <div class="space-y-2">
                                    <div>
                                        <span class="text-sm text-gray-500">Channel:</span>
                                        <p class="font-medium capitalize">{{ reminder.channel || '-' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Contact Number:</span>
                                        <p class="font-medium">{{ reminder.contact_number_used || '-' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Amount Details -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-800 mb-3">Amount Details</h3>
                                <div class="space-y-2">
                                    <div>
                                        <span class="text-sm text-gray-500">Total Amount Reminded:</span>
                                        <p class="font-medium">Rs. {{ reminder.total_amount_reminded || '0.00' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Months Reminded:</span>
                                        <p class="font-medium">{{ reminder.months_reminded || '-' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Timeline -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-800 mb-3">Timeline</h3>
                                <div class="space-y-2">
                                    <div>
                                        <span class="text-sm text-gray-500">Sent At:</span>
                                        <p class="font-medium">{{ reminder.sent_at || '-' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Promised Pay Date:</span>
                                        <p class="font-medium">{{ reminder.promised_pay_date || '-' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Follow Up Date:</span>
                                        <p class="font-medium">{{ reminder.follow_up_date || '-' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Outcome -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-800 mb-3">Outcome</h3>
                                <div class="space-y-2">
                                    <div>
                                        <span class="text-sm text-gray-500">Outcome:</span>
                                        <p class="font-medium capitalize">{{ reminder.outcome ? reminder.outcome.replace('_', ' ') : '-' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Outcome Date:</span>
                                        <p class="font-medium">{{ reminder.outcome_date || '-' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="bg-gray-50 p-4 rounded-lg md:col-span-2">
                                <h3 class="text-lg font-semibold text-gray-800 mb-3">Notes & Response</h3>
                                <div class="space-y-3">
                                    <div>
                                        <span class="text-sm text-gray-500">Response:</span>
                                        <p class="font-medium">{{ reminder.response || 'No response recorded' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Message Content:</span>
                                        <p class="font-medium">{{ reminder.message_content || 'No message content' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Notes:</span>
                                        <p class="font-medium">{{ reminder.notes || 'No notes' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end gap-4 mt-6 pt-6 border-t">
                            <Link :href="route('fee-reminders.edit', reminder.id)"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
                                Edit Reminder
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
