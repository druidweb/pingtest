<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import { watch } from 'vue'
import throttle from 'lodash/throttle'
import Icon from '@/Shared/Icon.vue'
import Layout from '@/Shared/Layout.vue'
import SearchFilter from '@/Shared/SearchFilter.vue'

const props = defineProps({
  filters: Object,
  users: Object,
})

const form = useForm({
  search: props.filters.search,
  trashed: props.filters.trashed,
})

watch(
  form,
  throttle(() => {
    router.get('/users', form, { preserveState: true })
  }, 150),
  { deep: true },
)

const reset = () => {
  form.reset()
}
</script>
<template>
  <Layout>
    <Head title="Users" />
    <h1 class="mb-8 text-3xl font-bold">Users</h1>
    <div class="flex items-center justify-between mb-6">
      <SearchFilter v-model="form.search" class="w-full max-w-md mr-4" @reset="reset">
        <label class="block text-gray-700">Role:</label>
        <select v-model="form.role" class="w-full mt-1 form-select">
          <option :value="null" />
          <option value="user">User</option>
          <option value="owner">Owner</option>
        </select>
        <label class="block mt-4 text-gray-700">Trashed:</label>
        <select v-model="form.trashed" class="w-full mt-1 form-select">
          <option :value="null" />
          <option value="with">With Trashed</option>
          <option value="only">Only Trashed</option>
        </select>
      </SearchFilter>
      <Link class="btn-indigo" href="/users/create">
        <span>Create</span>
        <span class="hidden md:inline">&nbsp;User</span>
      </Link>
    </div>
    <div class="overflow-x-auto bg-white rounded-md shadow">
      <table class="w-full whitespace-nowrap">
        <thead class="font-bold text-left">
          <tr>
            <th class="px-6 pt-6 pb-4">Name</th>
            <th class="px-6 pt-6 pb-4">Email</th>
            <th class="px-6 pt-6 pb-4" colspan="2">Role</th>
          </tr>
        </thead>
        <tr v-for="user in users" :key="user.id" class="hover:bg-gray-100 focus-within:bg-gray-100">
          <td class="border-t border-gray-300">
            <Link class="flex items-center px-6 py-4 focus:text-indigo-500" :href="`/users/${user.id}/edit`">
              <img v-if="user.photo" class="block w-5 h-5 mr-2 -my-2 rounded-full" :src="user.photo" />
              {{ user.name }}
              <Icon v-if="user.deleted_at" name="trash" class="w-3 h-3 ml-2 shrink-0 fill-gray-400" />
            </Link>
          </td>
          <td class="border-t border-gray-300">
            <Link class="flex items-center px-6 py-4" :href="`/users/${user.id}/edit`" tabindex="-1">
              {{ user.email }}
            </Link>
          </td>
          <td class="border-t border-gray-300">
            <Link class="flex items-center px-6 py-4" :href="`/users/${user.id}/edit`" tabindex="-1">
              {{ user.owner ? 'Owner' : 'User' }}
            </Link>
          </td>
          <td class="w-px border-t border-gray-300">
            <Link class="flex items-center px-4" :href="`/users/${user.id}/edit`" tabindex="-1">
              <Icon name="cheveron-right" class="block w-6 h-6 fill-gray-400" />
            </Link>
          </td>
        </tr>
        <tr v-if="users.length === 0">
          <td class="px-6 py-4 border-t border-gray-300" colspan="4">No users found.</td>
        </tr>
      </table>
    </div>
  </Layout>
</template>
