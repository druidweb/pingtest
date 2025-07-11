<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout.vue'
import TextInput from '@/Shared/TextInput.vue'
import SelectInput from '@/Shared/SelectInput.vue'
import LoadingButton from '@/Shared/LoadingButton.vue'
import TrashedMessage from '@/Shared/TrashedMessage.vue'

defineOptions({
  layout: Layout,
})

const props = defineProps({
  contact: Object,
  organizations: Array,
})

const form = useForm({
  first_name: props.contact.first_name,
  last_name: props.contact.last_name,
  organization_id: props.contact.organization_id,
  email: props.contact.email,
  phone: props.contact.phone,
  address: props.contact.address,
  city: props.contact.city,
  region: props.contact.region,
  country: props.contact.country,
  postal_code: props.contact.postal_code,
})

const update = () => {
  form.put(`/contacts/${props.contact.id}`)
}

const destroy = () => {
  if (confirm('Are you sure you want to delete this contact?')) {
    router.delete(`/contacts/${props.contact.id}`)
  }
}

const restore = () => {
  if (confirm('Are you sure you want to restore this contact?')) {
    router.put(`/contacts/${props.contact.id}/restore`)
  }
}
</script>
<template>
  <Head :title="`${form.first_name} ${form.last_name}`" />
  <h1 class="mb-8 text-3xl font-bold">
    <Link class="text-indigo-400 hover:text-indigo-600" href="/contacts">Contacts</Link>
    <span class="font-medium text-indigo-400">/</span>
    {{ form.first_name }} {{ form.last_name }}
  </h1>
  <TrashedMessage v-if="contact.deleted_at" class="mb-6" @restore="restore"> This contact has been deleted. </TrashedMessage>
  <div class="max-w-3xl overflow-hidden bg-white rounded-md shadow">
    <form @submit.prevent="update">
      <div class="flex flex-wrap p-8 -mb-8 -mr-6">
        <TextInput v-model="form.first_name" :error="form.errors.first_name" class="w-full pb-8 pr-6 lg:w-1/2" label="First name" />
        <TextInput v-model="form.last_name" :error="form.errors.last_name" class="w-full pb-8 pr-6 lg:w-1/2" label="Last name" />
        <SelectInput v-model="form.organization_id" :error="form.errors.organization_id" class="w-full pb-8 pr-6 lg:w-1/2" label="Organization">
          <option :value="null" />
          <option v-for="organization in organizations" :key="organization.id" :value="organization.id">
            {{ organization.name }}
          </option>
        </SelectInput>
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
        <button v-if="!contact.deleted_at" class="text-red-600 hover:underline" tabindex="-1" type="button" @click="destroy">Delete Contact</button>
        <LoadingButton :loading="form.processing" class="ml-auto btn-indigo" type="submit">Update Contact</LoadingButton>
      </div>
    </form>
  </div>
</template>
