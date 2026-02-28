<template>
  <AppLayout>
    <div class="min-h-screen flex flex-col">
      <div class="flex-1 px-3 sm:px-4 lg:px-8 py-4 sm:py-6 lg:py-8">

        <!-- Page Header -->
        <div class="mb-4 sm:mb-6 lg:mb-8">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
            <div>
              <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Create Branch</h1>
              <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-gray-600">Add a new branch</p>
            </div>
            <Button
              @click="$inertia.visit(route('branches.index'))"
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

        <!-- Create Form -->
        <div class="bg-white rounded-lg sm:rounded-xl shadow-md p-4 sm:p-6">
          <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">

              <!-- Branch Name -->
              <div>
                <label for="branch_name" class="block text-sm font-medium text-gray-700 mb-2">
                  Branch Name <span class="text-red-500">*</span>
                </label>
                <input
                  id="branch_name"
                  v-model="form.branch_name"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  :class="{ 'border-red-500': form.errors.branch_name }"
                  placeholder="e.g., Main Campus"
                  required
                />
                <p v-if="form.errors.branch_name" class="mt-1 text-sm text-red-600">
                  {{ form.errors.branch_name }}
                </p>
              </div>

              <!-- Location -->
              <div>
                <label for="location" class="block text-sm font-medium text-gray-700 mb-2">
                  Location <span class="text-red-500">*</span>
                </label>
                <input
                  id="location"
                  v-model="form.location"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  :class="{ 'border-red-500': form.errors.location }"
                  placeholder="e.g., Downtown Area"
                  required
                />
                <p v-if="form.errors.location" class="mt-1 text-sm text-red-600">
                  {{ form.errors.location }}
                </p>
              </div>

              <!-- Phone -->
              <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                  Phone <span class="text-red-500">*</span>
                </label>
                <input
                  id="phone"
                  v-model="form.phone"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  :class="{ 'border-red-500': form.errors.phone }"
                  placeholder="e.g., +92 300 1234567"
                  required
                />
                <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">
                  {{ form.errors.phone }}
                </p>
              </div>

              <!-- Status -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Status
                </label>
                <div class="flex items-center mt-3">
                  <input
                    id="is_active"
                    v-model="form.is_active"
                    type="checkbox"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                  />
                  <label for="is_active" class="ml-2 block text-sm text-gray-900">
                    Active
                  </label>
                </div>
                <p v-if="form.errors.is_active" class="mt-1 text-sm text-red-600">
                  {{ form.errors.is_active }}
                </p>
              </div>

              <!-- Classes Multi-Select -->
              <div class="md:col-span-2">
                <label for="class_ids" class="block text-sm font-medium text-gray-700 mb-2">
                  Assign Classes
                </label>
                <select
                  id="class_ids"
                  v-model="form.class_ids"
                  multiple
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  :class="{ 'border-red-500': form.errors.class_ids }"
                  size="10"
                >
                  <option v-for="classItem in classes" :key="classItem.id" :value="classItem.id">
                    {{ classItem.class_name }}
                  </option>
                </select>
                <p class="mt-1 text-xs text-gray-500">
                  Hold Ctrl (Windows) or Command (Mac) to select multiple classes
                </p>
                <p v-if="form.errors.class_ids" class="mt-1 text-sm text-red-600">
                  {{ form.errors.class_ids }}
                </p>
              </div>

              <!-- Selected Classes Display -->
              <div v-if="form.class_ids && form.class_ids.length > 0" class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Selected Classes ({{ form.class_ids.length }})
                </label>
                <div class="flex flex-wrap gap-2">
                  <span
                    v-for="classId in form.class_ids"
                    :key="classId"
                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800"
                  >
                    {{ getClassName(classId) }}
                    <button
                      type="button"
                      @click="removeClass(classId)"
                      class="ml-2 inline-flex items-center justify-center w-4 h-4 text-blue-600 hover:text-blue-800"
                    >
                      <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                      </svg>
                    </button>
                  </span>
                </div>
              </div>

            </div>

            <!-- Submit Buttons -->
            <div class="mt-6 flex flex-col sm:flex-row items-center justify-end gap-3 sm:gap-4">
              <Button
                type="button"
                variant="secondary"
                @click="$inertia.visit(route('branches.index'))"
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
                <span v-if="!form.processing">Create Branch</span>
                <span v-else>Creating...</span>
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

// Props
const props = defineProps({
  classes: {
    type: Array,
    default: () => []
  }
})

// Form - Initialize with empty defaults
const form = useForm({
  branch_name: '',
  location: '',
  phone: '',
  is_active: true,
  class_ids: []
})

// Helper functions
const getClassName = (classId) => {
  const classItem = props.classes.find(c => c.id === classId)
  return classItem ? classItem.class_name : ''
}

const removeClass = (classId) => {
  form.class_ids = form.class_ids.filter(id => id !== classId)
}

// Form submit
const submit = () => {
  form.post(route('branches.store'), {
    preserveScroll: true,
    onSuccess: () => {
      // Redirect will be handled by the controller
    }
  })
}
</script>