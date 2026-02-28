<template>
  <AppLayout>
    <div class="min-h-screen flex flex-col">
      <div class="flex-1 px-3 sm:px-4 lg:px-8 py-4 sm:py-6 lg:py-8">

        <!-- Page Header -->
        <div class="mb-4 sm:mb-6 lg:mb-8">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
            <div>
              <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Edit Academic Year</h1>
              <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-gray-600">Update academic year information</p>
            </div>
            <Button
              @click="$inertia.visit(route('academic-years.index'))"
              variant="secondary"
              class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
              </svg>
              Back to List
            </Button>
          </div>
        </div>

        <!-- Edit Form -->
        <div class="bg-white rounded-lg sm:rounded-xl shadow-md p-4 sm:p-6">
          <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">

              <!-- Year Name -->
              <div class="col-span-2">
                <label for="year_name" class="block text-sm font-medium text-gray-700 mb-2">
                  Year Name <span class="text-red-500">*</span>
                </label>
                <input
                  id="year_name"
                  v-model="form.year_name"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  :class="{ 'border-red-500': form.errors.year_name }"
                  placeholder="e.g., 2024-2025"
                  required
                />
                <p v-if="form.errors.year_name" class="mt-1 text-sm text-red-600">
                  {{ form.errors.year_name }}
                </p>
              </div>

              <!-- Start Date -->
              <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                  Start Date <span class="text-red-500">*</span>
                </label>
                <input
                  id="start_date"
                  v-model="form.start_date"
                  type="date"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  :class="{ 'border-red-500': form.errors.start_date }"
                  required
                />
                <p v-if="form.errors.start_date" class="mt-1 text-sm text-red-600">
                  {{ form.errors.start_date }}
                </p>
              </div>

              <!-- End Date -->
              <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                  End Date <span class="text-red-500">*</span>
                </label>
                <input
                  id="end_date"
                  v-model="form.end_date"
                  type="date"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  :class="{ 'border-red-500': form.errors.end_date }"
                  :min="form.start_date"
                  required
                />
                <p v-if="form.errors.end_date" class="mt-1 text-sm text-red-600">
                  {{ form.errors.end_date }}
                </p>
              </div>

              <!-- Duration Display -->
              <div v-if="duration" class="col-span-2">
                <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                  <p class="text-sm text-blue-800">
                    <span class="font-semibold">Duration:</span> {{ duration }} days
                  </p>
                </div>
              </div>

              <!-- Is Active -->
              <div class="col-span-2">
                <div class="flex items-center">
                  <input
                    id="is_active"
                    v-model="form.is_active"
                    type="checkbox"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                  />
                  <label for="is_active" class="ml-2 block text-sm text-gray-900">
                    Set as Active Academic Year
                  </label>
                </div>
                <p class="mt-1 text-sm text-gray-500">
                  Note: Setting this as active will automatically deactivate other academic years.
                </p>
              </div>

            </div>

            <!-- Submit Buttons -->
            <div class="mt-6 flex flex-col sm:flex-row items-center justify-end gap-3 sm:gap-4">
              <Button
                type="button"
                variant="secondary"
                @click="$inertia.visit(route('academic-years.index'))"
                class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm"
              >
                Cancel
              </Button>
              <Button
                type="submit"
                variant="primary"
                :loading="form.processing"
                class="w-full sm:w-auto shadow-lg hover:shadow-xl transition-all text-sm"
              >
                <span v-if="!form.processing">Update Academic Year</span>
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
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Button from '@/Components/Common/Button.vue'

// Props
const props = defineProps({
  academicYear: {
    type: Object,
    required: true
  }
})

// Form - Initialize with existing data
const form = useForm({
  year_name: props.academicYear.year_name,
  start_date: props.academicYear.start_date,
  end_date: props.academicYear.end_date,
  is_active: props.academicYear.is_active,
})

// Calculate duration
const duration = computed(() => {
  if (!form.start_date || !form.end_date) return null
  const start = new Date(form.start_date)
  const end = new Date(form.end_date)
  const diffTime = Math.abs(end - start)
  return Math.ceil(diffTime / (1000 * 60 * 60 * 24))
})

// Form submit
const submit = () => {
  form.put(route('academic-years.update', props.academicYear.id), {
    preserveScroll: true,
    onSuccess: () => {
      // Redirect handled by controller
    }
  })
}
</script>