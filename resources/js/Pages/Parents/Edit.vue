<template>
  <AppLayout>
    <div class="min-h-screen flex flex-col">
      <div class="flex-1 px-3 sm:px-4 lg:px-8 py-4 sm:py-6 lg:py-8">
        <div class="mb-4 sm:mb-6 lg:mb-8">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
            <div><h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Edit Parent</h1><p class="mt-1 sm:mt-2 text-xs sm:text-sm text-gray-600">Update parent/guardian information</p></div>
            <Button @click="$inertia.visit(route('parents.index'))" variant="secondary" class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm"><svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>Back to List</Button>
          </div>
        </div>
        <div class="bg-white rounded-lg sm:rounded-xl shadow-md overflow-hidden">
          <form @submit.prevent="submit">
            <!-- Father Info -->
            <div class="p-4 sm:p-6 lg:p-8 bg-gray-50 border-b border-gray-100">
              <div class="flex items-center mb-6"><div class="h-10 w-1 bg-green-600 rounded-full mr-4"></div><h2 class="text-xl font-semibold text-gray-900">Father's Information</h2></div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                <div><label for="father_name" class="block text-sm font-medium text-gray-700 mb-2">Father's Name <span class="text-red-500">*</span></label><input id="father_name" v-model="form.father_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" :class="{ 'border-red-500': form.errors.father_name }" required /><p v-if="form.errors.father_name" class="mt-1 text-sm text-red-600">{{ form.errors.father_name }}</p></div>
                <div><label for="father_cnic" class="block text-sm font-medium text-gray-700 mb-2">Father's CNIC <span class="text-red-500">*</span></label><input id="father_cnic" v-model="form.father_cnic" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" :class="{ 'border-red-500': form.errors.father_cnic }" required /><p v-if="form.errors.father_cnic" class="mt-1 text-sm text-red-600">{{ form.errors.father_cnic }}</p></div>
                <div><label for="father_phone" class="block text-sm font-medium text-gray-700 mb-2">Father's Phone <span class="text-red-500">*</span></label><input id="father_phone" v-model="form.father_phone" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" :class="{ 'border-red-500': form.errors.father_phone }" required /><p v-if="form.errors.father_phone" class="mt-1 text-sm text-red-600">{{ form.errors.father_phone }}</p></div>
                <div><label for="father_occupation" class="block text-sm font-medium text-gray-700 mb-2">Father's Occupation</label><input id="father_occupation" v-model="form.father_occupation" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" /></div>
              </div>
            </div>
            <!-- Mother Info -->
            <div class="p-4 sm:p-6 lg:p-8 border-b border-gray-100">
              <div class="flex items-center mb-6"><div class="h-10 w-1 bg-purple-600 rounded-full mr-4"></div><h2 class="text-xl font-semibold text-gray-900">Mother's Information</h2></div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                <div><label for="mother_name" class="block text-sm font-medium text-gray-700 mb-2">Mother's Name</label><input id="mother_name" v-model="form.mother_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" /></div>
                <div><label for="mother_cnic" class="block text-sm font-medium text-gray-700 mb-2">Mother's CNIC</label><input id="mother_cnic" v-model="form.mother_cnic" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" /></div>
                <div><label for="mother_phone" class="block text-sm font-medium text-gray-700 mb-2">Mother's Phone</label><input id="mother_phone" v-model="form.mother_phone" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" /></div>
              </div>
            </div>
            <!-- Address & Emergency -->
            <div class="p-4 sm:p-6 lg:p-8 bg-gray-50 border-b border-gray-100">
              <div class="flex items-center mb-6"><div class="h-10 w-1 bg-indigo-600 rounded-full mr-4"></div><h2 class="text-xl font-semibold text-gray-900">Address & Emergency Contact</h2></div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                <div class="md:col-span-2"><label for="address" class="block text-sm font-medium text-gray-700 mb-2">Complete Address <span class="text-red-500">*</span></label><textarea id="address" v-model="form.address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" :class="{ 'border-red-500': form.errors.address }" required></textarea><p v-if="form.errors.address" class="mt-1 text-sm text-red-600">{{ form.errors.address }}</p></div>
                <div><label for="city" class="block text-sm font-medium text-gray-700 mb-2">City <span class="text-red-500">*</span></label><input id="city" v-model="form.city" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" :class="{ 'border-red-500': form.errors.city }" required /><p v-if="form.errors.city" class="mt-1 text-sm text-red-600">{{ form.errors.city }}</p></div>
                <div><label for="emergency_contact_name" class="block text-sm font-medium text-gray-700 mb-2">Emergency Contact Name</label><input id="emergency_contact_name" v-model="form.emergency_contact_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" /></div>
                <div><label for="emergency_contact_phone" class="block text-sm font-medium text-gray-700 mb-2">Emergency Contact Phone</label><input id="emergency_contact_phone" v-model="form.emergency_contact_phone" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" /></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-2">Status</label><div class="flex items-center mt-3"><input id="is_active" v-model="form.is_active" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" /><label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label></div></div>
              </div>
            </div>
            <!-- Submit -->
            <div class="px-4 sm:px-6 lg:px-8 py-6 bg-gray-50 border-t border-gray-200">
              <div class="flex flex-col sm:flex-row items-center justify-end gap-3 sm:gap-4">
                <Button type="button" variant="secondary" @click="$inertia.visit(route('parents.index'))" class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm">Cancel</Button>
                <Button type="submit" variant="primary" :loading="form.processing" class="w-full sm:w-auto shadow-lg hover:shadow-xl transition-all text-sm"><span v-if="!form.processing">Update Parent</span><span v-else>Updating...</span></Button>
              </div>
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
const props = defineProps({ parent: { type: Object, required: true } })
const form = useForm({
  father_name: props.parent.father_name,
  father_cnic: props.parent.father_cnic,
  father_phone: props.parent.father_phone,
  father_occupation: props.parent.father_occupation || '',
  mother_name: props.parent.mother_name || '',
  mother_cnic: props.parent.mother_cnic || '',
  mother_phone: props.parent.mother_phone || '',
  address: props.parent.address,
  city: props.parent.city,
  emergency_contact_name: props.parent.emergency_contact_name || '',
  emergency_contact_phone: props.parent.emergency_contact_phone || '',
  is_active: props.parent.is_active,
})
const submit = () => { form.put(route('parents.update', props.parent.id), { preserveScroll: true }) }
</script>