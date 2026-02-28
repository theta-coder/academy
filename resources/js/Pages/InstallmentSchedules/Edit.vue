<template>
  <AppLayout>
    <div class="min-h-screen flex flex-col">
      <div class="flex-1 px-3 sm:px-4 lg:px-8 py-4 sm:py-6 lg:py-8">
        <div class="mb-4 sm:mb-6 lg:mb-8">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
            <div><h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Edit Installment Kist</h1><p class="mt-1 sm:mt-2 text-xs sm:text-sm text-gray-600">Update kist payment details</p></div>
            <Button @click="$inertia.visit(route('installment-schedules.index'))" variant="secondary" class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm"><svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>Back to List</Button>
          </div>
        </div>
        <div class="bg-white rounded-lg sm:rounded-xl shadow-md p-4 sm:p-6">
          <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
              <div>
                <label for="assignment_id" class="block text-sm font-medium text-gray-700 mb-2">Assignment <span class="text-red-500">*</span></label>
                <select id="assignment_id" v-model="form.assignment_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required><option value="">Select Assignment</option><option v-for="a in assignments" :key="a.id" :value="a.id">{{ a.student_enrollment?.student?.student_name || 'Assignment' }} #{{ a.id }} — {{ a.installment_plan?.plan_name }}</option></select>
                <p v-if="form.errors.assignment_id" class="mt-1 text-sm text-red-600">{{ form.errors.assignment_id }}</p>
              </div>
              <div>
                <label for="kist_number" class="block text-sm font-medium text-gray-700 mb-2">Kist Number <span class="text-red-500">*</span></label>
                <input id="kist_number" v-model="form.kist_number" type="number" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required />
                <p v-if="form.errors.kist_number" class="mt-1 text-sm text-red-600">{{ form.errors.kist_number }}</p>
              </div>
              <div>
                <label for="kist_amount" class="block text-sm font-medium text-gray-700 mb-2">Kist Amount <span class="text-red-500">*</span></label>
                <input id="kist_amount" v-model="form.kist_amount" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required />
                <p v-if="form.errors.kist_amount" class="mt-1 text-sm text-red-600">{{ form.errors.kist_amount }}</p>
              </div>
              <div>
                <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">Due Date <span class="text-red-500">*</span></label>
                <input id="due_date" v-model="form.due_date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required />
                <p v-if="form.errors.due_date" class="mt-1 text-sm text-red-600">{{ form.errors.due_date }}</p>
              </div>
              <div>
                <label for="paid_amount" class="block text-sm font-medium text-gray-700 mb-2">Paid Amount</label>
                <input id="paid_amount" v-model="form.paid_amount" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
              </div>
              <div>
                <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-2">Payment Date</label>
                <input id="payment_date" v-model="form.payment_date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
              </div>
              <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="status" v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"><option value="pending">Pending</option><option value="paid">Paid</option><option value="overdue">Overdue</option><option value="partial">Partial</option></select>
              </div>
              <div class="md:col-span-2">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                <textarea id="notes" v-model="form.notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
              </div>
            </div>
            <div class="mt-6 flex flex-col sm:flex-row items-center justify-end gap-3 sm:gap-4">
              <Button type="button" variant="secondary" @click="$inertia.visit(route('installment-schedules.index'))" class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm">Cancel</Button>
              <Button type="submit" variant="primary" :loading="form.processing" class="w-full sm:w-auto shadow-lg hover:shadow-xl transition-all text-sm"><span v-if="!form.processing">Update Kist</span><span v-else>Updating...</span></Button>
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
const props = defineProps({ schedule: { type: Object, required: true }, assignments: Array })
const form = useForm({
  assignment_id: props.schedule.assignment_id, kist_number: props.schedule.kist_number,
  kist_amount: props.schedule.kist_amount, due_date: props.schedule.due_date,
  paid_amount: props.schedule.paid_amount || '', payment_date: props.schedule.payment_date || '',
  status: props.schedule.status, notes: props.schedule.notes || '',
})
const submit = () => { form.put(route('installment-schedules.update', props.schedule.id), { preserveScroll: true }) }
</script>
