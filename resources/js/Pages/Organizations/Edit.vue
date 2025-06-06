<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import Icon from '@/Shared/Icon.vue'
import Layout from '@/Shared/Layout.vue'
import TextInput from '@/Shared/TextInput.vue'
import SelectInput from '@/Shared/SelectInput.vue'
import LoadingButton from '@/Shared/LoadingButton.vue'
import TrashedMessage from '@/Shared/TrashedMessage.vue'

defineOptions({
  layout: Layout,
})

const props = defineProps({
  organization: Object,
})

const form = useForm({
  name: props.organization.name,
  email: props.organization.email,
  phone: props.organization.phone,
  address: props.organization.address,
  city: props.organization.city,
  region: props.organization.region,
  country: props.organization.country,
  postal_code: props.organization.postal_code,
})

const update = () => {
  form.put(`/organizations/${props.organization.id}`)
}

const destroy = () => {
  if (confirm('Are you sure you want to delete this organization?')) {
    router.delete(`/organizations/${props.organization.id}`)
  }
}

const restore = () => {
  if (confirm('Are you sure you want to restore this organization?')) {
    router.put(`/organizations/${props.organization.id}/restore`)
  }
}
</script>
<template>
  <Head :title="form.name" />
  <h1 class="mb-8 text-3xl font-bold">
    <Link class="text-indigo-400 hover:text-indigo-600" href="/organizations">Organizations</Link>
    <span class="font-medium text-indigo-400">/</span>
    {{ form.name }}
  </h1>
  <TrashedMessage v-if="organization.deleted_at" class="mb-6" @restore="restore"> This organization has been deleted. </TrashedMessage>
  <div class="max-w-3xl overflow-hidden bg-white rounded-md shadow">
    <form @submit.prevent="update">
      <div class="flex flex-wrap p-8 -mb-8 -mr-6">
        <TextInput v-model="form.name" :error="form.errors.name" class="w-full pb-8 pr-6 lg:w-1/2" label="Name" />
        <TextInput v-model="form.email" :error="form.errors.email" class="w-full pb-8 pr-6 lg:w-1/2" label="Email" />
        <TextInput v-model="form.phone" :error="form.errors.phone" class="w-full pb-8 pr-6 lg:w-1/2" label="Phone" />
        <TextInput v-model="form.address" :error="form.errors.address" class="w-full pb-8 pr-6 lg:w-1/2" label="Address" />
        <TextInput v-model="form.city" :error="form.errors.city" class="w-full pb-8 pr-6 lg:w-1/2" label="City" />
        <TextInput v-model="form.region" :error="form.errors.region" class="w-full pb-8 pr-6 lg:w-1/2" label="Province/State" />
        <SelectInput v-model="form.country" :error="form.errors.country" class="w-full pb-8 pr-6 lg:w-1/2" label="Country">
          <option :value="null" />
          <option value="CA">Canada</option>
          <option value="US">United States</option>
        </SelectInput>
        <TextInput v-model="form.postal_code" :error="form.errors.postal_code" class="w-full pb-8 pr-6 lg:w-1/2" label="Postal code" />
      </div>
      <div class="flex items-center px-8 py-4 border-t border-gray-100 bg-gray-50">
        <button v-if="!organization.deleted_at" class="text-red-600 hover:underline" tabindex="-1" type="button" @click="destroy">Delete Organization</button>
        <LoadingButton :loading="form.processing" class="ml-auto btn-indigo" type="submit">Update Organization</LoadingButton>
      </div>
    </form>
  </div>
  <h2 class="mt-12 text-2xl font-bold">Contacts</h2>
  <div class="mt-6 overflow-x-auto bg-white rounded shadow">
    <table class="w-full whitespace-nowrap">
      <thead class="font-bold text-left">
        <tr>
          <th class="px-6 pt-6 pb-4">Name</th>
          <th class="px-6 pt-6 pb-4">City</th>
          <th class="px-6 pt-6 pb-4" colspan="2">Phone</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="contact in organization.contacts" :key="contact.id" class="hover:bg-gray-100 focus-within:bg-gray-100">
          <td class="border-t border-gray-300">
            <Link class="flex items-center px-6 py-4 focus:text-indigo-500" :href="`/contacts/${contact.id}/edit`">
              {{ contact.name }}
              <Icon v-if="contact.deleted_at" name="trash" class="w-3 h-3 ml-2 shrink-0 fill-gray-400" />
            </Link>
          </td>
          <td class="border-t border-gray-300">
            <Link class="flex items-center px-6 py-4" :href="`/contacts/${contact.id}/edit`" tabindex="-1">
              {{ contact.city }}
            </Link>
          </td>
          <td class="border-t border-gray-300">
            <Link class="flex items-center px-6 py-4" :href="`/contacts/${contact.id}/edit`" tabindex="-1">
              {{ contact.phone }}
            </Link>
          </td>
          <td class="w-px border-t border-gray-300">
            <Link class="flex items-center px-4" :href="`/contacts/${contact.id}/edit`" tabindex="-1">
              <Icon name="cheveron-right" class="block w-6 h-6 fill-gray-400" />
            </Link>
          </td>
        </tr>
        <tr v-if="organization.contacts.length === 0">
          <td class="px-6 py-4 border-t border-gray-300" colspan="4">No contacts found.</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
