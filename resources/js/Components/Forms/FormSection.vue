<template>
  <div class="md:col-span-2 mt-4 mb-1">
    <div class="flex items-center gap-3">
      <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest whitespace-nowrap">
        {{ title }}
      </h3>
      <span class="h-px flex-1 bg-slate-100 dark:bg-slate-700"></span>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, required: true },
})
</script>
