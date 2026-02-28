<template>
  <AppLayout>
    <div class="min-h-screen flex flex-col">
      <div class="flex-1 px-3 sm:px-4 lg:px-8 py-4 sm:py-6 lg:py-8">
        <div class="mb-4 sm:mb-6 lg:mb-8">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
            <div><h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Create Payment Account</h1><p class="mt-1 sm:mt-2 text-xs sm:text-sm text-gray-600">Add a new bank or mobile payment account</p></div>
            <Button @click="$inertia.visit(route('academy-payment-accounts.index'))" variant="secondary" class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm"><svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>Back to List</Button>
          </div>
        </div>
        <div class="bg-white rounded-lg sm:rounded-xl shadow-md p-4 sm:p-6">
          <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
              <div>
                <label for="account_title" class="block text-sm font-medium text-gray-700 mb-2">Account Title <span class="text-red-500">*</span></label>
                <input id="account_title" v-model="form.account_title" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" :class="{ 'border-red-500': form.errors.account_title }" placeholder="e.g., Academy Main Account" required />
                <p v-if="form.errors.account_title" class="mt-1 text-sm text-red-600">{{ form.errors.account_title }}</p>
              </div>
              <div>
                <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">Payment Method <span class="text-red-500">*</span></label>
                <select id="payment_method" v-model="form.payment_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" :class="{ 'border-red-500': form.errors.payment_method }" required>
                  <option value="">Select Method</option>
                  <option value="bank_transfer">Bank Transfer</option><option value="jazzcash">JazzCash</option><option value="easypaisa">Easypaisa</option><option value="cash">Cash</option>
                </select>
                <p v-if="form.errors.payment_method" class="mt-1 text-sm text-red-600">{{ form.errors.payment_method }}</p>
              </div>
              <div>
                <label for="account_number" class="block text-sm font-medium text-gray-700 mb-2">Account Number <span class="text-red-500">*</span></label>
                <input id="account_number" v-model="form.account_number" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" :class="{ 'border-red-500': form.errors.account_number }" placeholder="e.g., 1234567890" required />
                <p v-if="form.errors.account_number" class="mt-1 text-sm text-red-600">{{ form.errors.account_number }}</p>
              </div>
              <div>
                <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-2">Bank Name</label>
                <input id="bank_name" v-model="form.bank_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="e.g., HBL" />
              </div>
              <div>
                <label for="branch_name" class="block text-sm font-medium text-gray-700 mb-2">Branch Name</label>
                <input id="branch_name" v-model="form.branch_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="e.g., Main Branch" />
              </div>
              <div>
                <label for="iban" class="block text-sm font-medium text-gray-700 mb-2">IBAN</label>
                <input id="iban" v-model="form.iban" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="e.g., PK36SCBL0000001123456702" />
              </div>
              <div class="md:col-span-2">
                <label for="instructions" class="block text-sm font-medium text-gray-700 mb-2">Payment Instructions</label>
                <textarea id="instructions" v-model="form.instructions" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Instructions for making payments..."></textarea>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <div class="flex items-center mt-3"><input id="is_active" v-model="form.is_active" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" /><label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label></div>
              </div>
            </div>
            <div class="mt-6 flex flex-col sm:flex-row items-center justify-end gap-3 sm:gap-4">
              <Button type="button" variant="secondary" @click="$inertia.visit(route('academy-payment-accounts.index'))" class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm">Cancel</Button>
              <Button type="submit" variant="primary" :loading="form.processing" class="w-full sm:w-auto shadow-lg hover:shadow-xl transition-all text-sm"><span v-if="!form.processing">Create Account</span><span v-else>Creating...</span></Button>
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
const form = useForm({ account_title: '', payment_method: '', account_number: '', bank_name: '', branch_name: '', iban: '', instructions: '', is_active: true })
const submit = () => { form.post(route('academy-payment-accounts.store'), { preserveScroll: true }) }
</script>
