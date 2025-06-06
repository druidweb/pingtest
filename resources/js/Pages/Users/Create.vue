<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout.vue'
import FileInput from '@/Shared/FileInput.vue'
import TextInput from '@/Shared/TextInput.vue'
import SelectInput from '@/Shared/SelectInput.vue'
import LoadingButton from '@/Shared/LoadingButton.vue'

defineOptions({
  layout: Layout,
})
const form = useForm({
  first_name: '',
  last_name: '',
  email: '',
  password: '',
  owner: false,
  photo: null,
})

const store = () => {
  form.post('/users')
}
</script>
<template>
  <Head title="Create User" />
  <h1 class="mb-8 text-3xl font-bold">
    <Link class="text-indigo-400 hover:text-indigo-600" href="/users">Users</Link>
    <span class="font-medium text-indigo-400">/</span> Create
  </h1>
  <div class="max-w-3xl overflow-hidden bg-white rounded-md shadow">
    <form @submit.prevent="store">
      <div class="flex flex-wrap p-8 -mb-8 -mr-6">
        <TextInput v-model="form.first_name" :error="form.errors.first_name" class="w-full pb-8 pr-6 lg:w-1/2" label="First name" />
        <TextInput v-model="form.last_name" :error="form.errors.last_name" class="w-full pb-8 pr-6 lg:w-1/2" label="Last name" />
        <TextInput v-model="form.email" :error="form.errors.email" class="w-full pb-8 pr-6 lg:w-1/2" label="Email" />
        <TextInput v-model="form.password" :error="form.errors.password" class="w-full pb-8 pr-6 lg:w-1/2" type="password" autocomplete="new-password" label="Password" />
        <SelectInput v-model="form.owner" :error="form.errors.owner" class="w-full pb-8 pr-6 lg:w-1/2" label="Owner">
          <option :value="true">Yes</option>
          <option :value="false">No</option>
        </SelectInput>
        <FileInput v-model="form.photo" :error="form.errors.photo" class="w-full pb-8 pr-6 lg:w-1/2" type="file" accept="image/*" label="Photo" />
      </div>
      <div class="flex items-center justify-end px-8 py-4 border-t border-gray-100 bg-gray-50">
        <LoadingButton :loading="form.processing" class="btn-indigo" type="submit">Create User</LoadingButton>
      </div>
    </form>
  </div>
</template>
