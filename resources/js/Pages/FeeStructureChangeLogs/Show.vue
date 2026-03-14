<script setup>
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    log: Object,
});
</script>

<template>
    <Head title="Change Log Details" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold text-gray-800">Change Log Details</h2>
                            <Link :href="route('fee-structure-change-logs.index')"
                                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-colors">
                                Back to List
                            </Link>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Structure Info -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-800 mb-3">Structure Information</h3>
                                <div class="space-y-2">
                                    <div>
                                        <span class="text-sm text-gray-500">Fee Type:</span>
                                        <p class="font-medium">{{ log.fee_structure?.fee_type?.fee_name || '-' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Class:</span>
                                        <p class="font-medium">{{ log.fee_structure?.class?.class_name || '-' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Branch:</span>
                                        <p class="font-medium">{{ log.fee_structure?.branch?.branch_name || '-' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Amount Change -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-800 mb-3">Amount Change</h3>
                                <div class="space-y-2">
                                    <div>
                                        <span class="text-sm text-gray-500">Old Amount:</span>
                                        <p class="font-medium">Rs. {{ log.old_amount }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">New Amount:</span>
                                        <p class="font-medium">Rs. {{ log.new_amount }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Difference:</span>
                                        <p :class="['font-medium', log.new_amount > log.old_amount ? 'text-red-600' : 'text-green-600']">
                                            {{ log.new_amount > log.old_amount ? '+' : '' }}Rs. {{ (log.new_amount - log.old_amount).toFixed(2) }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Due Day Change -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-800 mb-3">Due Day Change</h3>
                                <div class="space-y-2">
                                    <div>
                                        <span class="text-sm text-gray-500">Old Due Day:</span>
                                        <p class="font-medium">Day {{ log.old_due_day || '-' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">New Due Day:</span>
                                        <p class="font-medium">Day {{ log.new_due_day || '-' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Effective Date -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-800 mb-3">Effective Date</h3>
                                <p class="font-medium">{{ log.effective_from || '-' }}</p>
                            </div>

                            <!-- Change Reason -->
                            <div class="bg-gray-50 p-4 rounded-lg md:col-span-2">
                                <h3 class="text-lg font-semibold text-gray-800 mb-3">Change Reason</h3>
                                <p class="font-medium">{{ log.change_reason || 'No reason provided' }}</p>
                            </div>

                            <!-- Affects Existing -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-800 mb-3">Affects Existing Vouchers</h3>
                                <p :class="['font-medium', log.affects_existing_vouchers ? 'text-yellow-600' : 'text-green-600']">
                                    {{ log.affects_existing_vouchers ? 'Yes' : 'No' }}
                                </p>
                            </div>

                            <!-- Changed By -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-800 mb-3">Changed By</h3>
                                <p class="font-medium">{{ log.changed_by?.name || '-' }}</p>
                                <p class="text-sm text-gray-500">{{ log.changed_at }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
