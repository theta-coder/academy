<template>
  <AppLayout>
    <div class="min-h-screen flex flex-col">
      <div class="flex-1 px-3 sm:px-4 lg:px-8 py-4 sm:py-6 lg:py-8">

        <div class="mb-4 sm:mb-6 lg:mb-8">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
            <div>
              <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Edit Fee Structure</h1>
              <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-gray-600">Update fee structure details</p>
            </div>
            <Button @click="$inertia.visit(route('fee-structures.index'))" variant="secondary" class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
              Back to List
            </Button>
          </div>
        </div>

        <div class="bg-white rounded-lg sm:rounded-xl shadow-md p-4 sm:p-6">
          <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">

              <div>
                <label for="academic_year_id" class="block text-sm font-medium text-gray-700 mb-2">Academic Year <span class="text-red-500">*</span></label>
                <select id="academic_year_id" v-model="form.academic_year_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                  <option value="">Select Academic Year</option>
                  <option v-for="y in academicYears" :key="y.id" :value="y.id">{{ y.year_name }}</option>
                </select>
                <p v-if="form.errors.academic_year_id" class="mt-1 text-sm text-red-600">{{ form.errors.academic_year_id }}</p>
              </div>

              <div>
                <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-2">Branch <span class="text-red-500">*</span></label>
                <select id="branch_id" v-model="form.branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                  <option value="">Select Branch</option>
                  <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.branch_name }}</option>
                </select>
                <p v-if="form.errors.branch_id" class="mt-1 text-sm text-red-600">{{ form.errors.branch_id }}</p>
              </div>

              <div>
                <label for="class_id" class="block text-sm font-medium text-gray-700 mb-2">Class <span class="text-red-500">*</span></label>
                <select id="class_id" v-model="form.class_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                  <option value="">Select Class</option>
                  <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.class_name }}</option>
                </select>
                <p v-if="form.errors.class_id" class="mt-1 text-sm text-red-600">{{ form.errors.class_id }}</p>
              </div>

              <div>
                <label for="fee_type_id" class="block text-sm font-medium text-gray-700 mb-2">Fee Type <span class="text-red-500">*</span></label>
                <select id="fee_type_id" v-model="form.fee_type_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                  <option value="">Select Fee Type</option>
                  <option v-for="f in feeTypes" :key="f.id" :value="f.id">{{ f.fee_name }}</option>
                </select>
                <p v-if="form.errors.fee_type_id" class="mt-1 text-sm text-red-600">{{ form.errors.fee_type_id }}</p>
              </div>

              <div>
                <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Amount (Rs.) <span class="text-red-500">*</span></label>
                <input id="amount" v-model="form.amount" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required />
                <p v-if="form.errors.amount" class="mt-1 text-sm text-red-600">{{ form.errors.amount }}</p>
              </div>

              <div>
                <label for="due_day" class="block text-sm font-medium text-gray-700 mb-2">Due Day of Month</label>
                <input id="due_day" v-model="form.due_day" type="number" min="1" max="28" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
              </div>

              <div>
                <label for="effective_from" class="block text-sm font-medium text-gray-700 mb-2">Effective From</label>
                <input id="effective_from" v-model="form.effective_from" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
              </div>

              <div>
                <label for="effective_to" class="block text-sm font-medium text-gray-700 mb-2">Effective To</label>
                <input id="effective_to" v-model="form.effective_to" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
              </div>

              <div class="flex items-center">
                <input id="is_active" v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                <label for="is_active" class="ml-2 block text-sm text-gray-700">Active</label>
              </div>

            </div>

            <div class="mt-6 flex flex-col sm:flex-row items-center justify-end gap-3 sm:gap-4">
              <Button type="button" variant="secondary" @click="$inertia.visit(route('fee-structures.index'))" class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm">Cancel</Button>
              <Button type="submit" variant="primary" :loading="form.processing" class="w-full sm:w-auto shadow-lg hover:shadow-xl transition-all text-sm">
                <span v-if="!form.processing">Update Fee Structure</span>
                <span v-else>Updating...</span>
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

const props = defineProps({
  feeStructure: { type: Object, required: true },
  academicYears: Array,
  branches: Array,
  classes: Array,
  feeTypes: Array,
})

const form = useForm({
  academic_year_id: props.feeStructure.academic_year_id,
  branch_id: props.feeStructure.branch_id,
  class_id: props.feeStructure.class_id,
  fee_type_id: props.feeStructure.fee_type_id,
  amount: props.feeStructure.amount,
  due_day: props.feeStructure.due_day ?? '',
  effective_from: props.feeStructure.effective_from ?? '',
  effective_to: props.feeStructure.effective_to ?? '',
  is_active: props.feeStructure.is_active ?? true,
})

const submit = () => {
  form.put(route('fee-structures.update', props.feeStructure.id), {
    preserveScroll: true,
  })
}
</script>
