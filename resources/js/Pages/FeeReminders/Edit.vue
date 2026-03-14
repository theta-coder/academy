<script setup>
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    reminder: Object,
    students: Array,
});

const form = useForm({
    student_enrollment_id: props.reminder.student_enrollment_id || '',
    voucher_ids: props.reminder.voucher_ids || '',
    total_amount_reminded: props.reminder.total_amount_reminded || '',
    months_reminded: props.reminder.months_reminded || '',
    channel: props.reminder.channel || 'sms',
    message_content: props.reminder.message_content || '',
    contact_number_used: props.reminder.contact_number_used || '',
    sent_at: props.reminder.sent_at || '',
    response: props.reminder.response || '',
    promised_pay_date: props.reminder.promised_pay_date || '',
    follow_up_date: props.reminder.follow_up_date || '',
    outcome: props.reminder.outcome || '',
    outcome_date: props.reminder.outcome_date || '',
    notes: props.reminder.notes || '',
});

const channels = [
    { value: 'sms', label: 'SMS' },
    { value: 'email', label: 'Email' },
    { value: 'whatsapp', label: 'WhatsApp' },
    { value: 'call', label: 'Call' },
];

const outcomes = [
    { value: 'promised_to_pay', label: 'Promised to Pay' },
    { value: 'no_response', label: 'No Response' },
    { value: 'follow_up', label: 'Follow Up' },
    { value: 'paid', label: 'Paid' },
];

const submit = () => {
    form.put(route('fee-reminders.update', props.reminder.id));
};
</script>

<template>
    <Head title="Edit Fee Reminder" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">Edit Fee Reminder</h2>

                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Channel -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Channel *</label>
                                <select v-model="form.channel" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option v-for="channel in channels" :key="channel.value" :value="channel.value">
                                        {{ channel.label }}
                                    </option>
                                </select>
                                <span v-if="form.errors.channel" class="text-red-500 text-sm">
                                    {{ form.errors.channel }}
                                </span>
                            </div>

                            <!-- Amount -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Total Amount Reminded (Rs.)
                                    *</label>
                                <input v-model.number="form.total_amount_reminded" type="number" step="0.01" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                <span v-if="form.errors.total_amount_reminded" class="text-red-500 text-sm">
                                    {{ form.errors.total_amount_reminded }}
                                </span>
                            </div>

                            <!-- Months Reminded -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Months Reminded</label>
                                <input v-model="form.months_reminded" type="text"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>

                            <!-- Contact Number -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Contact Number Used</label>
                                <input v-model="form.contact_number_used" type="text"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>

                            <!-- Message Content -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Message Content</label>
                                <textarea v-model="form.message_content" rows="3"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                            </div>

                            <!-- Sent At -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Sent At</label>
                                <input v-model="form.sent_at" type="datetime-local"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>

                            <!-- Response -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Response</label>
                                <textarea v-model="form.response" rows="2"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                            </div>

                            <!-- Promised Pay Date -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Promised Payment Date</label>
                                <input v-model="form.promised_pay_date" type="date"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>

                            <!-- Follow Up Date -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Follow Up Date</label>
                                <input v-model="form.follow_up_date" type="date"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>

                            <!-- Outcome -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Outcome</label>
                                <select v-model="form.outcome"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select Outcome</option>
                                    <option v-for="outcome in outcomes" :key="outcome.value" :value="outcome.value">
                                        {{ outcome.label }}
                                    </option>
                                </select>
                            </div>

                            <!-- Notes -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                <textarea v-model="form.notes" rows="2"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                            </div>

                            <!-- Actions -->
                            <div class="flex justify-end gap-4 pt-4">
                                <Link :href="route('fee-reminders.index')"
                                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-colors">
                                    Cancel
                                </Link>
                                <button type="submit" :disabled="form.processing"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors disabled:opacity-50">
                                    Update Reminder
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
