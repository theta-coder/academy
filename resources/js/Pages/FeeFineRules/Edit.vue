<template>
  <AppLayout>
    <div class="min-h-screen flex flex-col">
      <div class="flex-1 px-3 sm:px-4 lg:px-8 py-4 sm:py-6 lg:py-8">
        <div class="mb-4 sm:mb-6 lg:mb-8">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
            <div>
              <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Edit Fine Rule</h1>
              <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-gray-600">Update late payment fine rule</p>
            </div>
            <Button @click="$inertia.visit(route('fee-fine-rules.index'))" variant="secondary" class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
              Back to List
            </Button>
          </div>
        </div>
        <div class="bg-white rounded-lg sm:rounded-xl shadow-md p-4 sm:p-6">
          <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
              <div>
                <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                <select id="branch_id" v-model="form.branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                  <option value="">All Branches</option>
                  <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.branch_name }}</option>
                </select>
              </div>
              <div>
                <label for="fee_type_id" class="block text-sm font-medium text-gray-700 mb-2">Fee Type</label>
                <select id="fee_type_id" v-model="form.fee_type_id" :disabled="form.applies_to_all_fee_types" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm disabled:bg-gray-100">
                  <option value="">Select Fee Type</option>
                  <option v-for="f in feeTypes" :key="f.id" :value="f.id">{{ f.fee_name }}</option>
                </select>
              </div>
              <div class="flex items-center">
                <input id="applies_to_all_fee_types" v-model="form.applies_to_all_fee_types" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                <label for="applies_to_all_fee_types" class="ml-2 block text-sm text-gray-700">Applies to All Fee Types</label>
              </div>
              <div>
                <label for="days_after_due" class="block text-sm font-medium text-gray-700 mb-2">Days After Due <span class="text-red-500">*</span></label>
                <input id="days_after_due" v-model="form.days_after_due" type="number" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required />
              </div>
              <div>
                <label for="fine_type" class="block text-sm font-medium text-gray-700 mb-2">Fine Type <span class="text-red-500">*</span></label>
                <select id="fine_type" v-model="form.fine_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                  <option value="fixed">Fixed</option><option value="percentage">Percentage</option><option value="daily_fixed">Daily Fixed</option><option value="daily_percentage">Daily Percentage</option>
                </select>
              </div>
              <div>
                <label for="fine_value" class="block text-sm font-medium text-gray-700 mb-2">Fine Value <span class="text-red-500">*</span></label>
                <input id="fine_value" v-model="form.fine_value" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required />
              </div>
              <div>
                <label for="max_fine" class="block text-sm font-medium text-gray-700 mb-2">Max Fine</label>
                <input id="max_fine" v-model="form.max_fine" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
              </div>
              <div class="flex items-center">
                <input id="is_active" v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                <label for="is_active" class="ml-2 block text-sm text-gray-700">Active</label>
              </div>
              <div class="col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea id="description" v-model="form.description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
              </div>
            </div>
            <div class="mt-6 flex flex-col sm:flex-row items-center justify-end gap-3 sm:gap-4">
              <Button type="button" variant="secondary" @click="$inertia.visit(route('fee-fine-rules.index'))" class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm">Cancel</Button>
              <Button type="submit" variant="primary" :loading="form.processing" class="w-full sm:w-auto shadow-lg hover:shadow-xl transition-all text-sm">
                <span v-if="!form.processing">Update Fine Rule</span><span v-else>Updating...</span>
              </Button>
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
const props = defineProps({ fineRule: { type: Object, required: true }, branches: Array, feeTypes: Array })
const form = useForm({ branch_id: props.fineRule.branch_id ?? '', fee_type_id: props.fineRule.fee_type_id ?? '', applies_to_all_fee_types: props.fineRule.applies_to_all_fee_types ?? false, days_after_due: props.fineRule.days_after_due, fine_type: props.fineRule.fine_type, fine_value: props.fineRule.fine_value, max_fine: props.fineRule.max_fine ?? '', description: props.fineRule.description ?? '', is_active: props.fineRule.is_active ?? true })
const submit = () => { form.put(route('fee-fine-rules.update', props.fineRule.id), { preserveScroll: true }) }
</script>
