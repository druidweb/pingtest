<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import { watch } from 'vue'
import throttle from 'lodash/throttle'
import Icon from '@/Shared/Icon.vue'
import Layout from '@/Shared/Layout.vue'
import Pagination from '@/Shared/Pagination.vue'
import SearchFilter from '@/Shared/SearchFilter.vue'

defineOptions({
  layout: Layout,
})

const props = defineProps({
  filters: Object,
  organizations: Object,
})

const form = useForm({
  search: props.filters.search,
  trashed: props.filters.trashed,
})

watch(
  form,
  throttle(() => {
    router.get('/organizations', form, { preserveState: true })
  }, 150),
  { deep: true },
)

const reset = () => {
  form.reset()
}
</script>
<template>
  <Head title="Organizations" />
  <h1 class="mb-8 text-3xl font-bold">Organizations</h1>
  <div class="flex items-center justify-between mb-6">
    <SearchFilter v-model="form.search" class="w-full max-w-md mr-4" @reset="reset">
      <label class="block text-gray-700">Trashed:</label>
      <select v-model="form.trashed" class="w-full mt-1 form-select">
        <option :value="null" />
        <option value="with">With Trashed</option>
        <option value="only">Only Trashed</option>
      </select>
    </SearchFilter>
    <Link class="btn-indigo" href="/organizations/create">
      <span>Create</span>
      <span class="hidden md:inline">&nbsp;Organization</span>
    </Link>
  </div>
  <div class="overflow-x-auto bg-white rounded-md shadow">
    <table class="w-full whitespace-nowrap">
      <thead class="font-bold text-left">
        <tr>
          <th class="px-6 pt-6 pb-4">Name</th>
          <th class="px-6 pt-6 pb-4">City</th>
          <th class="px-6 pt-6 pb-4" colspan="2">Phone</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="organization in organizations.data" :key="organization.id" class="hover:bg-gray-100 focus-within:bg-gray-100">
          <td class="border-t border-gray-300">
            <Link class="flex items-center px-6 py-4 focus:text-indigo-500" :href="`/organizations/${organization.id}/edit`">
              {{ organization.name }}
              <Icon v-if="organization.deleted_at" name="trash" class="w-3 h-3 ml-2 shrink-0 fill-gray-400" />
            </Link>
          </td>
          <td class="border-t border-gray-300">
            <Link class="flex items-center px-6 py-4" :href="`/organizations/${organization.id}/edit`" tabindex="-1">
              {{ organization.city }}
            </Link>
          </td>
          <td class="border-t border-gray-300">
            <Link class="flex items-center px-6 py-4" :href="`/organizations/${organization.id}/edit`" tabindex="-1">
              {{ organization.phone }}
            </Link>
          </td>
          <td class="w-px border-t border-gray-300">
            <Link class="flex items-center px-4" :href="`/organizations/${organization.id}/edit`" tabindex="-1">
              <Icon name="cheveron-right" class="block w-6 h-6 fill-gray-400" />
            </Link>
          </td>
        </tr>
        <tr v-if="organizations.data.length === 0">
          <td class="px-6 py-4 border-t border-gray-300" colspan="4">No organizations found.</td>
        </tr>
      </tbody>
    </table>
  </div>
  <Pagination class="mt-6" :links="organizations.links" />
</template>
