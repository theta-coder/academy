<template>
  <AppLayout>
    <div class="min-h-screen flex flex-col">
      <div class="flex-1 px-3 sm:px-4 lg:px-8 py-4 sm:py-6 lg:py-8">
        <div class="mb-4 sm:mb-6 lg:mb-8">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
            <div>
              <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Concession Type Details</h1>
              <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-gray-600">View concession type information</p>
            </div>
            <div class="flex gap-2 sm:gap-3">
              <Button @click="$inertia.visit(route('fee-concession-types.edit', concessionType.id))" variant="primary" class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>Edit
              </Button>
              <Button @click="$inertia.visit(route('fee-concession-types.index'))" variant="secondary" class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>Back to List
              </Button>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg sm:rounded-xl shadow-md overflow-hidden">
          <div class="p-4 sm:p-6 lg:p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 lg:gap-8">
              <!-- Concession Name -->
              <div class="bg-gray-50 rounded-lg p-4">
                <dt class="text-xs sm:text-sm font-medium text-gray-500 uppercase tracking-wide">Concession Name</dt>
                <dd class="mt-1 text-base sm:text-lg font-semibold text-gray-900">{{ concessionType.concession_name }}</dd>
              </div>

              <!-- Discount Type -->
              <div class="bg-gray-50 rounded-lg p-4">
                <dt class="text-xs sm:text-sm font-medium text-gray-500 uppercase tracking-wide">Discount Type</dt>
                <dd class="mt-2">
                  <span :class="concessionType.discount_type === 'percentage' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'" class="px-3 py-1 text-sm font-medium rounded-full">
                    {{ concessionType.discount_type === 'percentage' ? 'Percentage' : 'Fixed Amount' }}
                  </span>
                </dd>
              </div>

              <!-- Default Discount Value -->
              <div class="bg-gray-50 rounded-lg p-4">
                <dt class="text-xs sm:text-sm font-medium text-gray-500 uppercase tracking-wide">Default Discount Value</dt>
                <dd class="mt-1 text-base sm:text-lg font-semibold text-gray-900">
                  {{ concessionType.discount_type === 'percentage' ? concessionType.default_discount_value + '%' : 'Rs. ' + concessionType.default_discount_value }}
                </dd>
              </div>

              <!-- Applies To -->
              <div v-if="concessionType.applies_to" class="bg-gray-50 rounded-lg p-4">
                <dt class="text-xs sm:text-sm font-medium text-gray-500 uppercase tracking-wide">Applies To</dt>
                <dd class="mt-1 text-base sm:text-lg font-medium text-gray-900">
                  <span class="px-2 py-1 text-xs font-medium rounded-full bg-indigo-100 text-indigo-800">
                    {{ concessionType.applies_to === 'all_fees' ? 'All Fees' : concessionType.applies_to === 'monthly_only' ? 'Monthly Only' : 'Specific Fees' }}
                  </span>
                </dd>
              </div>

              <!-- Status -->
              <div class="bg-gray-50 rounded-lg p-4">
                <dt class="text-xs sm:text-sm font-medium text-gray-500 uppercase tracking-wide">Status</dt>
                <dd class="mt-2">
                  <span :class="concessionType.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'" class="px-3 py-1 text-sm font-medium rounded-full">
                    {{ concessionType.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </dd>
              </div>

              <!-- Description -->
              <div v-if="concessionType.description" class="bg-gray-50 rounded-lg p-4 md:col-span-2">
                <dt class="text-xs sm:text-sm font-medium text-gray-500 uppercase tracking-wide mb-2">Description</dt>
                <dd class="mt-1 text-sm sm:text-base text-gray-700 whitespace-pre-line">{{ concessionType.description }}</dd>
              </div>
            </div>

            <!-- Additional Info -->
            <div class="mt-6 sm:mt-8 pt-6 sm:pt-8 border-t border-gray-200">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <div>
                  <dt class="text-xs sm:text-sm font-medium text-gray-500">Created At</dt>
                  <dd class="mt-1 text-sm sm:text-base text-gray-900">{{ new Date(concessionType.created_at).toLocaleString() }}</dd>
                </div>
                <div>
                  <dt class="text-xs sm:text-sm font-medium text-gray-500">Updated At</dt>
                  <dd class="mt-1 text-sm sm:text-base text-gray-900">{{ new Date(concessionType.updated_at).toLocaleString() }}</dd>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Button from '@/Components/Common/Button.vue'

defineProps({
  concessionType: {
    type: Object,
    required: true
  }
})
</script>
