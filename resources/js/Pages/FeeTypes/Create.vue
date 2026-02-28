<template>
  <AppLayout>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      
      <!-- Page Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Create New Fee Type</h1>
            <p class="mt-2 text-sm text-gray-600">Add a new fee category to your fee structure</p>
          </div>
          <Link :href="route('fee-types.index')">
            <Button variant="secondary" class="shadow-sm hover:shadow-md transition-shadow">
              <ArrowLeftIcon class="h-5 w-5 mr-2" />
              Back to List
            </Button>
          </Link>
        </div>
      </div>

      <!-- Form Card -->
      <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <form @submit.prevent="submit">
          
          <!-- Basic Information Section -->
          <div class="p-8 border-b border-gray-100">
            <div class="flex items-center mb-6">
              <div class="h-10 w-1 bg-blue-600 rounded-full mr-4"></div>
              <h2 class="text-xl font-semibold text-gray-900">Basic Information</h2>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <Input
                  v-model="form.fee_name"
                  label="Fee Type Name"
                  placeholder="e.g., Tuition Fee"
                  required
                  :error="form.errors.fee_name"
                  class="transition-all duration-200"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Fee Category
                </label>
                <select
                  v-model="form.fee_category"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                  :class="{ 'border-red-500 focus:ring-red-500': form.errors.fee_category }"
                >
                  <option value="" disabled>Select category</option>
                  <option value="school">School</option>
                  <option value="academy">Academy</option>
                  <option value="both">Both</option>
                </select>
                <p v-if="form.errors.fee_category" class="mt-1 text-sm text-red-600">
                  {{ form.errors.fee_category }}
                </p>
              </div>
            </div>
          </div>

          <!-- Fee Details Section -->
          <div class="p-8 bg-gray-50 border-b border-gray-100">
            <div class="flex items-center mb-6">
              <div class="h-10 w-1 bg-green-600 rounded-full mr-4"></div>
              <h2 class="text-xl font-semibold text-gray-900">Fee Details</h2>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="md:col-span-2 flex items-center">
                <label class="flex items-center space-x-3 cursor-pointer group">
                  <input
                    v-model="form.is_recurring"
                    type="checkbox"
                    class="w-5 h-5 text-blue-602 border-gray-300 rounded focus:ring-2 focus:ring-blue-500 transition-all"
                  />
                  <div>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900">
                      Recurring Fee
                    </span>
                    <p class="text-xs text-gray-500 mt-0.5">Check if this fee repeats every specific months</p>
                  </div>
                </label>
              </div>

              <div v-if="form.is_recurring">
                <Input
                  v-model="form.recurring_months"
                  label="Recurring Months"
                  placeholder="e.g., 1,2,3,4,5,6,7,8,9,10,11,12"
                  :error="form.errors.recurring_months"
                  hint="Comma-separated month numbers (1-12)"
                />
              </div>

              <div>
                <Input
                  v-model="form.display_order"
                  type="number"
                  label="Display Order"
                  placeholder="e.g., 1"
                  :error="form.errors.display_order"
                  min="0"
                  hint="Determines the display position"
                />
              </div>
            </div>
          </div>

          <!-- Additional Information Section -->
          <div class="p-8 border-b border-gray-100">
            <div class="flex items-center mb-6">
              <div class="h-10 w-1 bg-orange-600 rounded-full mr-4"></div>
              <h2 class="text-xl font-semibold text-gray-900">Additional Information</h2>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="md:col-span-2">
                <Textarea
                  v-model="form.description"
                  label="Description"
                  placeholder="Enter detailed description of this fee type"
                  :rows="4"
                  :error="form.errors.description"
                  hint="Provide information about what this fee covers"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Status <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.is_active"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                  :class="{ 'border-red-500 focus:ring-red-500': form.errors.is_active }"
                >
                  <option :value="true">Active</option>
                  <option :value="false">Inactive</option>
                </select>
                <p v-if="form.errors.is_active" class="mt-1 text-sm text-red-600">
                  {{ form.errors.is_active }}
                </p>
              </div>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="px-8 py-6 bg-gray-50 border-t border-gray-200">
            <div class="flex justify-end gap-3">
              <Link :href="route('fee-types.index')">
                <Button 
                  type="button" 
                  variant="secondary" 
                  class="px-6 py-2.5 shadow-sm hover:shadow-md transition-all"
                >
                  Cancel
                </Button>
              </Link>
              <Button 
                type="submit" 
                variant="primary" 
                :loading="form.processing"
                class="px-8 py-2.5 shadow-md hover:shadow-lg transition-all"
              >
                <span v-if="!form.processing">Create Fee Type</span>
                <span v-else>Creating...</span>
              </Button>
            </div>
          </div>
        </form>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Button from '@/Components/Common/Button.vue'
import Input from '@/Components/Forms/Input.vue'
import Textarea from '@/Components/Forms/Textarea.vue'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

const form = useForm({
  fee_name: '',
  fee_category: '',
  is_recurring: false,
  recurring_months: '',
  description: '',
  display_order: '',
  is_active: true
})

const submit = () => {
  form.post(route('fee-types.store'), {
    preserveScroll: true,
    onSuccess: () => {
      // Handle success
    },
    onError: (errors) => {
      console.log('Validation errors:', errors)
    }
  })
}
</script>