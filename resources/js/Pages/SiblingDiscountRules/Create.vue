<template>
  <AppLayout>
    <div class="min-h-screen flex flex-col">
      <div class="flex-1 px-3 sm:px-4 lg:px-8 py-4 sm:py-6 lg:py-8">
        <div class="mb-4 sm:mb-6 lg:mb-8">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
            <div><h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Create Sibling Discount Rule</h1><p class="mt-1 sm:mt-2 text-xs sm:text-sm text-gray-600">Add a new sibling-based discount rule</p></div>
            <Button @click="$inertia.visit(route('sibling-discount-rules.index'))" variant="secondary" class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm"><svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>Back to List</Button>
          </div>
        </div>
        <div class="bg-white rounded-lg sm:rounded-xl shadow-md p-4 sm:p-6">
          <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
              <div>
                <label for="child_number" class="block text-sm font-medium text-gray-700 mb-2">Child Number <span class="text-red-500">*</span></label>
                <input id="child_number" v-model="form.child_number" type="number" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" :class="{ 'border-red-500': form.errors.child_number }" placeholder="e.g., 2 (for 2nd child)" required />
                <p v-if="form.errors.child_number" class="mt-1 text-sm text-red-600">{{ form.errors.child_number }}</p>
              </div>
              <div>
                <label for="applies_to_fee_type_id" class="block text-sm font-medium text-gray-700 mb-2">Applies To Fee Type</label>
                <select id="applies_to_fee_type_id" v-model="form.applies_to_fee_type_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
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
              <div class="md:col-span-2"><label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label><textarea id="description" v-model="form.description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Rule description..."></textarea></div>
              <div><label class="block text-sm font-medium text-gray-700 mb-2">Status</label><div class="flex items-center mt-3"><input id="is_active" v-model="form.is_active" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" /><label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label></div></div>
            </div>
            <div class="mt-6 flex flex-col sm:flex-row items-center justify-end gap-3 sm:gap-4">
              <Button type="button" variant="secondary" @click="$inertia.visit(route('sibling-discount-rules.index'))" class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm">Cancel</Button>
              <Button type="submit" variant="primary" :loading="form.processing" class="w-full sm:w-auto shadow-lg hover:shadow-xl transition-all text-sm"><span v-if="!form.processing">Create Rule</span><span v-else>Creating...</span></Button>
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

const props = defineProps({ feeTypes: { type: Array, default: () => [] } })
const form = useForm({ child_number: '', discount_type: '', discount_value: '', applies_to_fee_type_id: '', description: '', is_active: true })
const submit = () => { form.post(route('sibling-discount-rules.store'), { preserveScroll: true }) }
</script>
