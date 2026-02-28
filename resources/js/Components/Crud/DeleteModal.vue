<template>
  <Modal :show="show" @close="$emit('close')">
    <template #title>
      <div class="flex items-center">
        <div class="flex-shrink-0 w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-red-100 flex items-center justify-center mr-3 sm:mr-4">
          <svg class="w-5 h-5 sm:w-6 sm:h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>
        <span class="text-base sm:text-lg font-semibold text-gray-900">{{ title }}</span>
      </div>
    </template>
    <p class="text-xs sm:text-sm text-gray-600 mt-2">{{ message }}</p>
    <template #footer>
      <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3">
        <Button variant="secondary" @click="$emit('close')" class="w-full sm:w-auto px-4 sm:px-6 shadow-sm hover:shadow-md transition-all text-sm">Cancel</Button>
        <Button variant="danger" @click="$emit('confirm')" :loading="deleting" class="w-full sm:w-auto px-4 sm:px-6 shadow-md hover:shadow-lg transition-all text-sm">
          <span v-if="!deleting">Delete</span><span v-else>Deleting...</span>
        </Button>
      </div>
    </template>
  </Modal>
</template>

<script setup>
import Modal from '@/Components/Common/Modal.vue'
import Button from '@/Components/Common/Button.vue'

defineProps({
  show: { type: Boolean, default: false },
  deleting: { type: Boolean, default: false },
  title: { type: String, default: 'Delete Record' },
  message: { type: String, default: 'Are you sure you want to delete this record? This action cannot be undone.' },
})

defineEmits(['close', 'confirm'])
</script>
