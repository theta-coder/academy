<template>
  <AppLayout>
    <div class="min-h-screen flex flex-col">
      <div class="flex-1 px-3 sm:px-4 lg:px-8 py-4 sm:py-6 lg:py-8">
        <div class="mb-4 sm:mb-6 lg:mb-8">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
            <div>
              <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Edit Concession Type</h1>
              <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-gray-600">Update concession type information</p>
            </div>
            <Button @click="$inertia.visit(route('fee-concession-types.index'))" variant="secondary" class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>Back to List
            </Button>
          </div>
        </div>
        <div class="bg-white rounded-lg sm:rounded-xl shadow-md p-4 sm:p-6">
          <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
              <div>
                <label for="concession_name" class="block text-sm font-medium text-gray-700 mb-2">Concession Name <span class="text-red-500">*</span></label>
                <input id="concession_name" v-model="form.concession_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" :class="{ 'border-red-500': form.errors.concession_name }" required />
                <p v-if="form.errors.concession_name" class="mt-1 text-sm text-red-600">{{ form.errors.concession_name }}</p>
              </div>
              <div>
                <label for="discount_type" class="block text-sm font-medium text-gray-700 mb-2">Discount Type <span class="text-red-500">*</span></label>
                <select id="discount_type" v-model="form.discount_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" :class="{ 'border-red-500': form.errors.discount_type }" required>
                  <option value="">Select Type</option>
                  <option value="percentage">Percentage</option>
                  <option value="fixed">Fixed Amount</option>
                </select>
                <p v-if="form.errors.discount_type" class="mt-1 text-sm text-red-600">{{ form.errors.discount_type }}</p>
              </div>
              <div>
                <label for="default_discount_value" class="block text-sm font-medium text-gray-700 mb-2">Default Discount Value <span class="text-red-500">*</span></label>
                <input id="default_discount_value" v-model="form.default_discount_value" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" :class="{ 'border-red-500': form.errors.default_discount_value }" required />
                <p v-if="form.errors.default_discount_value" class="mt-1 text-sm text-red-600">{{ form.errors.default_discount_value }}</p>
              </div>
              <div>
                <label for="applies_to" class="block text-sm font-medium text-gray-700 mb-2">Applies To</label>
                <input id="applies_to" v-model="form.applies_to" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                <p v-if="form.errors.applies_to" class="mt-1 text-sm text-red-600">{{ form.errors.applies_to }}</p>
              </div>
              <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea id="description" v-model="form.description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <div class="flex items-center mt-3">
                  <input id="is_active" v-model="form.is_active" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" />
                  <label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label>
                </div>
              </div>
            </div>
            <div class="mt-6 flex flex-col sm:flex-row items-center justify-end gap-3 sm:gap-4">
              <Button type="button" variant="secondary" @click="$inertia.visit(route('fee-concession-types.index'))" class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm">Cancel</Button>
              <Button type="submit" variant="primary" :loading="form.processing" class="w-full sm:w-auto shadow-lg hover:shadow-xl transition-all text-sm">
                <span v-if="!form.processing">Update Concession Type</span><span v-else>Updating...</span>
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

const props = defineProps({ feeConcessionType: { type: Object, required: true } })

const form = useForm({
  concession_name: props.feeConcessionType.concession_name,
  discount_type: props.feeConcessionType.discount_type,
  default_discount_value: props.feeConcessionType.default_discount_value,
  applies_to: props.feeConcessionType.applies_to,
  description: props.feeConcessionType.description,
  is_active: props.feeConcessionType.is_active,
})

const submit = () => {
  form.put(route('fee-concession-types.update', props.feeConcessionType.id), { preserveScroll: true })
}
</script>
