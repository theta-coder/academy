<script setup>
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    balance: Object,
});

const getStatusClass = (status) => {
    const classes = {
        unpaid: 'bg-red-100 text-red-800',
        partial: 'bg-yellow-100 text-yellow-800',
        cleared: 'bg-green-100 text-green-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Previous Year Balance Details" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold text-gray-800">Previous Year Balance Details</h2>
                            <Link :href="route('previous-year-balances.index')"
                                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-colors">
                                Back to List
                            </Link>
                        </div>

                        <!-- Status Badge -->
                        <div class="mb-6">
                            <span :class="['px-3 py-1 text-sm font-medium rounded-full', getStatusClass(balance.status)]">
                                {{ balance.status }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Student Info -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-800 mb-3">Student Information</h3>
                                <div class="space-y-2">
                                    <div>
                                        <span class="text-sm text-gray-500">Student Name:</span>
                                        <p class="font-medium">{{ balance.student_enrollment?.student?.student_name || '-' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Admission No:</span>
                                        <p class="font-medium">{{ balance.student_enrollment?.student?.admission_no || '-' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Academic Years -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-800 mb-3">Academic Years</h3>
                                <div class="space-y-2">
                                    <div>
                                        <span class="text-sm text-gray-500">From Year:</span>
                                        <p class="font-medium">{{ balance.from_academic_year?.year_name || '-' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">To Year:</span>
                                        <p class="font-medium">{{ balance.to_academic_year?.year_name || '-' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Amount Details -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-800 mb-3">Amount Details</h3>
                                <div class="space-y-2">
                                    <div>
                                        <span class="text-sm text-gray-500">Original Outstanding:</span>
                                        <p class="font-medium">Rs. {{ balance.original_outstanding || '0.00' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Recovered Amount:</span>
                                        <p class="font-medium text-green-600">Rs. {{ balance.recovered_amount || '0.00' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Remaining Amount:</span>
                                        <p :class="['font-medium', balance.remaining_amount > 0 ? 'text-red-600' : 'text-green-600']">
                                            Rs. {{ balance.remaining_amount || '0.00' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Timeline -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-800 mb-3">Timeline</h3>
                                <div class="space-y-2">
                                    <div>
                                        <span class="text-sm text-gray-500">Carry Forward Date:</span>
                                        <p class="font-medium">{{ balance.carry_forward_date || '-' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Carried Forward By:</span>
                                        <p class="font-medium">{{ balance.carried_forward_by?.name || '-' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="bg-gray-50 p-4 rounded-lg md:col-span-2">
                                <h3 class="text-lg font-semibold text-gray-800 mb-3">Notes / Breakup</h3>
                                <p class="font-medium">{{ balance.notes || 'No notes provided' }}</p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end gap-4 mt-6 pt-6 border-t">
                            <Link :href="route('previous-year-balances.edit', balance.id)"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
                                Edit Balance
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
