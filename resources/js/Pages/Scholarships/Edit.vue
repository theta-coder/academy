<template>
  <AppLayout>
    <div class="min-h-screen flex flex-col">
      <div class="flex-1 px-3 sm:px-4 lg:px-8 py-4 sm:py-6 lg:py-8">
        <div class="mb-4 sm:mb-6 lg:mb-8">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
            <div>
              <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Edit Scholarship</h1>
              <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-gray-600">Update scholarship program details</p>
            </div>
            <Button @click="$inertia.visit(route('scholarships.index'))" variant="secondary" class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>Back to List
            </Button>
          </div>
        </div>
        <div class="bg-white rounded-lg sm:rounded-xl shadow-md p-4 sm:p-6">
          <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
              <div>
                <label for="scholarship_name" class="block text-sm font-medium text-gray-700 mb-2">Scholarship Name <span class="text-red-500">*</span></label>
                <input id="scholarship_name" v-model="form.scholarship_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" :class="{ 'border-red-500': form.errors.scholarship_name }" required />
                <p v-if="form.errors.scholarship_name" class="mt-1 text-sm text-red-600">{{ form.errors.scholarship_name }}</p>
              </div>
              <div>
                <label for="applicable_fee_type_id" class="block text-sm font-medium text-gray-700 mb-2">Applicable Fee Type</label>
                <select id="applicable_fee_type_id" v-model="form.applicable_fee_type_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                  <option value="">All Fee Types</option>
                  <option v-for="ft in feeTypes" :key="ft.id" :value="ft.id">{{ ft.fee_name }}</option>
                </select>
              </div>
              <div>
                <label for="discount_type" class="block text-sm font-medium text-gray-700 mb-2">Discount Type <span class="text-red-500">*</span></label>
                <select id="discount_type" v-model="form.discount_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                  <option value="">Select Type</option><option value="percentage">Percentage</option><option value="fixed">Fixed Amount</option>
                </select>
                <p v-if="form.errors.discount_type" class="mt-1 text-sm text-red-600">{{ form.errors.discount_type }}</p>
              </div>
              <div>
                <label for="discount_value" class="block text-sm font-medium text-gray-700 mb-2">Discount Value <span class="text-red-500">*</span></label>
                <input id="discount_value" v-model="form.discount_value" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" :class="{ 'border-red-500': form.errors.discount_value }" required />
                <p v-if="form.errors.discount_value" class="mt-1 text-sm text-red-600">{{ form.errors.discount_value }}</p>
              </div>
              <div>
                <label for="criteria" class="block text-sm font-medium text-gray-700 mb-2">Criteria</label>
                <input id="criteria" v-model="form.criteria" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
              </div>
              <div>
                <label for="applies_to" class="block text-sm font-medium text-gray-700 mb-2">Applies To</label>
                <select id="applies_to" v-model="form.applies_to" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                  <option value="">Select Option</option>
                  <option value="all_fees">All Fees</option>
                  <option value="specific_fee_type">Specific Fee Type</option>
                  <option value="tuition_only">Tuition Only</option>
                </select>
                <p v-if="form.errors.applies_to" class="mt-1 text-sm text-red-600">{{ form.errors.applies_to }}</p>
              </div>
              <div>
                <label for="max_recipients" class="block text-sm font-medium text-gray-700 mb-2">Max Recipients</label>
                <input id="max_recipients" v-model="form.max_recipients" type="number" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Options</label>
                <div class="flex items-center gap-6 mt-3">
                  <div class="flex items-center"><input id="is_renewable" v-model="form.is_renewable" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" /><label for="is_renewable" class="ml-2 block text-sm text-gray-900">Renewable</label></div>
                  <div class="flex items-center"><input id="is_active" v-model="form.is_active" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" /><label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label></div>
                </div>
              </div>
              <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea id="description" v-model="form.description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
              </div>
            </div>
            <div class="mt-6 flex flex-col sm:flex-row items-center justify-end gap-3 sm:gap-4">
              <Button type="button" variant="secondary" @click="$inertia.visit(route('scholarships.index'))" class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm">Cancel</Button>
              <Button type="submit" variant="primary" :loading="form.processing" class="w-full sm:w-auto shadow-lg hover:shadow-xl transition-all text-sm"><span v-if="!form.processing">Update Scholarship</span><span v-else>Updating...</span></Button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Button from '@/Components/Common/Button.vue'

const props = defineProps({ scholarship: { type: Object, required: true }, feeTypes: { type: Array, default: () => [] } })

const form = useForm({
  scholarship_name: props.scholarship.scholarship_name, criteria: props.scholarship.criteria,
  discount_type: props.scholarship.discount_type, discount_value: props.scholarship.discount_value,
  applies_to: props.scholarship.applies_to, applicable_fee_type_id: props.scholarship.applicable_fee_type_id || '',
  max_recipients: props.scholarship.max_recipients, is_renewable: props.scholarship.is_renewable,
  description: props.scholarship.description, is_active: props.scholarship.is_active,
})

const submit = () => { form.put(route('scholarships.update', props.scholarship.id), { preserveScroll: true }) }
</script>
