<script setup>
import Icon from '@/Shared/Icon.vue'

defineEmits(['restore'])
</script>
<template>
  <div class="flex items-center justify-between max-w-3xl p-4 bg-yellow-400 rounded">
    <div class="flex items-center">
      <Icon name="trash" class="w-4 h-4 mr-2 shrink-0 fill-yellow-800" />
      <div class="text-sm font-medium text-yellow-800">
        <slot />
      </div>
    </div>
    <button class="text-sm text-yellow-800 hover:underline" tabindex="-1" type="button" @click="$emit('restore')">Restore</button>
  </div>
</template>
