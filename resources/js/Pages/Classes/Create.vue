<template>
  <AppLayout>
    <div class="min-h-screen flex flex-col">
      <div class="flex-1 px-3 sm:px-4 lg:px-8 py-4 sm:py-6 lg:py-8">

        <!-- Page Header -->
        <div class="mb-4 sm:mb-6 lg:mb-8">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
            <div>
              <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Create Class</h1>
              <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-gray-600">Add a new academy class</p>
            </div>
            <Button
              @click="$inertia.visit(route('classes.index'))"
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

              <!-- Class Name -->
              <div>
                <label for="class_name" class="block text-sm font-medium text-gray-700 mb-2">
                  Class Name <span class="text-red-500">*</span>
                </label>
                <input
                  id="class_name"
                  v-model="form.class_name"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  :class="{ 'border-red-500': form.errors.class_name }"
                  placeholder="e.g., Grade 1, Nursery, KG"
                  required
                />
                <p v-if="form.errors.class_name" class="mt-1 text-sm text-red-600">
                  {{ form.errors.class_name }}
                </p>
              </div>

              <!-- Display Order -->
              <div>
                <label for="display_order" class="block text-sm font-medium text-gray-700 mb-2">
                  Display Order
                </label>
                <input
                  id="display_order"
                  v-model="form.display_order"
                  type="number"
                  min="0"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  :class="{ 'border-red-500': form.errors.display_order }"
                  placeholder="e.g., 1, 2, 3"
                />
                <p v-if="form.errors.display_order" class="mt-1 text-sm text-red-600">
                  {{ form.errors.display_order }}
                </p>
                <p class="mt-1 text-xs text-gray-500">Lower numbers appear first in the list</p>
              </div>

              <!-- Is Active -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <div class="flex items-center mt-3">
                  <input
                    id="is_active"
                    v-model="form.is_active"
                    type="checkbox"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                  />
                  <label for="is_active" class="ml-2 block text-sm text-gray-900">Active Class</label>
                </div>
                <p v-if="form.errors.is_active" class="mt-1 text-sm text-red-600">
                  {{ form.errors.is_active }}
                </p>
              </div>

            </div>

            <!-- Submit Buttons -->
            <div class="mt-6 flex flex-col sm:flex-row items-center justify-end gap-3 sm:gap-4">
              <Button
                type="button"
                variant="secondary"
                @click="$inertia.visit(route('classes.index'))"
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
                <span v-if="!form.processing">Create Class</span>
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

// Form
const form = useForm({
  class_name: '',
  display_order: null,
  is_active: true,
})

// Form submit
const submit = () => {
  form.post(route('classes.store'), {
    preserveScroll: true,
    onSuccess: () => {
      // Redirect handled by controller
    }
  })
}
</script>