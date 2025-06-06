<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import Icon from '@/Shared/Icon.vue'
import Logo from '@/Shared/Logo.vue'
import Dropdown from '@/Shared/Dropdown.vue'
import MainMenu from '@/Shared/MainMenu.vue'
import FlashMessages from '@/Shared/FlashMessages.vue'

const auth = usePage().props.auth
</script>
<template>
  <div>
    <div id="dropdown" />
    <div class="md:flex md:flex-col">
      <div class="md:flex md:flex-col md:h-screen">
        <div class="md:flex md:shrink-0">
          <div class="flex items-center justify-between px-6 py-4 bg-indigo-900 md:shrink-0 md:justify-center md:w-56">
            <Link class="mt-1" href="/">
              <Logo class="fill-white" width="120" height="28" />
            </Link>
            <Dropdown class="md:hidden" align="right">
              <template #default>
                <svg class="w-6 h-6 fill-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                  <path d="M0 3h20v2H0V3zm0 6h20v2H0V9zm0 6h20v2H0v-2z" />
                </svg>
              </template>
              <template #dropdown>
                <div class="px-8 py-4 mt-2 bg-indigo-800 rounded shadow-lg">
                  <MainMenu />
                </div>
              </template>
            </Dropdown>
          </div>
          <div class="flex items-center justify-between w-full p-4 text-sm bg-white border border-gray-300 md:text-md md:px-12 md:py-0">
            <div class="mt-1 mr-4">{{ auth.user.account.name }}</div>
            <Dropdown class="mt-1" placement="bottom-end" width="48">
              <template #default>
                <div class="flex items-center cursor-pointer select-none group">
                  <div class="mr-1 text-gray-700 group-hover:text-indigo-600 focus:text-indigo-600 whitespace-nowrap">
                    <span>{{ auth.user.first_name }}</span>
                    <span class="hidden md:inline">&nbsp;{{ auth.user.last_name }}</span>
                  </div>
                  <icon class="w-5 h-5 fill-gray-700 group-hover:fill-indigo-600 focus:fill-indigo-600" name="cheveron-down" />
                </div>
              </template>
              <template #dropdown>
                <div class="py-2 mt-2 text-sm bg-white rounded shadow-xl">
                  <Link class="block px-6 py-2 hover:text-white hover:bg-indigo-500" :href="`/users/${auth.user.id}/edit`"> My Profile </Link>
                  <Link class="block px-6 py-2 hover:text-white hover:bg-indigo-500" href="/users">Manage Users</Link>
                  <Link class="block w-full px-6 py-2 text-left cursor-pointer hover:text-white hover:bg-indigo-500" href="/logout" method="delete" as="button"> Logout </Link>
                </div>
              </template>
            </Dropdown>
          </div>
        </div>
        <div class="md:flex md:grow md:overflow-hidden">
          <MainMenu class="hidden w-56 p-12 overflow-y-auto bg-indigo-800 shrink-0 md:block" />
          <div class="px-4 py-8 md:flex-1 md:p-12 md:overflow-y-auto" scroll-region>
            <FlashMessages />
            <slot />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
