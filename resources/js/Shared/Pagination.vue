<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  links: Array,
})
</script>
<template>
  <div v-if="links.length > 3">
    <div class="flex flex-wrap -mb-1">
      <template v-for="(link, key) in links">
        <div v-if="link.url === null" :key="key" class="px-4 py-3 mb-1 mr-1 text-sm leading-4 text-gray-400 border border-gray-300 rounded" v-html="link.label" />
        <Link v-else :key="`link-${key}`" class="px-4 py-3 mb-1 mr-1 text-sm leading-4 border border-gray-300 rounded focus:text-indigo-500 hover:bg-white focus:border-indigo-500" :class="{ 'bg-white': link.active }" :href="link.url">
          <span v-html="link.label" />
        </Link>
      </template>
    </div>
  </div>
</template>
