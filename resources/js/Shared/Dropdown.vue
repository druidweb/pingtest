<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'

const props = defineProps({
  align: {
    type: String,
    default: 'right',
  },
  width: {
    type: String,
    default: 'fit',
  },
  contentClasses: {
    type: String,
    default: '',
  },
  autoClose: {
    type: Boolean,
    default: true,
  },
  show: {
    type: Boolean,
    default: null,
  },
})

const emit = defineEmits(['update:show'])

const closeOnEscape = (e) => {
  if (isOpen.value && e.key === 'Escape') {
    isOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('keydown', closeOnEscape)
  if (props.autoClose) {
    document.addEventListener('click', closeOnClickAway)
  }
})

onUnmounted(() => {
  document.removeEventListener('keydown', closeOnEscape)
  document.removeEventListener('click', closeOnClickAway)
})

const widthClass = computed(() => {
  return {
    fit: 'w-fit',
    48: 'w-48',
    64: 'w-64',
    96: 'w-96',
  }[props.width.toString()]
})

const alignmentClasses = computed(() => {
  if (props.align === 'left') {
    return 'ltr:origin-top-left rtl:origin-top-right start-0'
  } else if (props.align === 'right') {
    return 'ltr:origin-top-right rtl:origin-top-left end-0'
  } else {
    return 'origin-top'
  }
})

// Create a local state if no v-model is provided
const localShow = ref(false)

// Use a computed property with getter/setter for v-model support
const isOpen = computed({
  get: () => (props.show !== null ? props.show : localShow.value),
  set: (value) => {
    if (props.show !== null) {
      emit('update:show', value)
    } else {
      localShow.value = value
    }
  },
})

const dropdown = ref(null)

const closeOnClickAway = (e) => {
  if (isOpen.value && dropdown.value && !dropdown.value.contains(e.target)) {
    isOpen.value = false
  }
}

// Handle clicks inside the dropdown content
const handleContentClick = (e) => {
  // If autoClose is true and the click is on a link or button, close the dropdown
  if (props.autoClose) {
    const isClickableElement = e.target.tagName === 'A' || e.target.tagName === 'BUTTON' || e.target.closest('a') || e.target.closest('button')

    if (isClickableElement) {
      isOpen.value = false
    }
  }
}
</script>

<template>
  <div class="relative" ref="dropdown">
    <div @click="isOpen = !isOpen" class="flex h-full cursor-pointer">
      <slot />
    </div>

    <!-- Full Screen Dropdown Overlay -->
    <div v-if="isOpen && !autoClose" class="fixed inset-0 z-40" @click="isOpen = false" />

    <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="scale-95 opacity-0" enter-to-class="scale-100 opacity-100" leave-active-class="transition duration-75 ease-in" leave-from-class="scale-100 opacity-100" leave-to-class="scale-95 opacity-0">
      <div v-show="isOpen" class="absolute z-50 rounded-md shadow-lg" :class="[widthClass, alignmentClasses]" style="display: none">
        <div class="border border-gray-300 rounded-md" :class="contentClasses" @click="handleContentClick">
          <slot name="dropdown" />
        </div>
      </div>
    </Transition>
  </div>
</template>
