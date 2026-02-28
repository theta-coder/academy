<template>
  <AppLayout>
    <div class="min-h-screen flex flex-col">
      <div class="flex-1 px-3 sm:px-4 lg:px-8 py-4 sm:py-6 lg:py-8">
        <div class="mb-4 sm:mb-6 lg:mb-8">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
            <div>
              <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ title }}</h1>
              <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-gray-600">{{ description }}</p>
            </div>
            <Button @click="$inertia.visit(backRoute)" variant="secondary" class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>Back to List
            </Button>
          </div>
        </div>
        <div class="bg-white rounded-lg sm:rounded-xl shadow-md p-4 sm:p-6">
          <form @submit.prevent="$emit('submit')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
              <slot />
            </div>
            <div class="mt-6 flex flex-col sm:flex-row items-center justify-end gap-3 sm:gap-4">
              <Button type="button" variant="secondary" @click="$inertia.visit(backRoute)" class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm">Cancel</Button>
              <Button type="submit" variant="primary" :loading="processing" class="w-full sm:w-auto shadow-lg hover:shadow-xl transition-all text-sm">
                <span v-if="!processing">{{ submitLabel }}</span><span v-else>{{ processingLabel || 'Saving...' }}</span>
              </Button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Button from '@/Components/Common/Button.vue'

defineProps({
  title: { type: String, required: true },
  description: { type: String, default: '' },
  backRoute: { type: String, required: true },
  submitLabel: { type: String, default: 'Save' },
  processingLabel: { type: String, default: 'Saving...' },
  processing: { type: Boolean, default: false },
})

defineEmits(['submit'])
</script>
