<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps({
  links: Array,
});
</script>

<template>
  <nav class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
    <div class="flex flex-1 justify-between sm:hidden">
      <Link
        v-if="links[0].url"
        :href="links[0].url"
        class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
      >
        Anterior
      </Link>
      <Link
        v-if="links[links.length - 1].url"
        :href="links[links.length - 1].url"
        class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
      >
        Próximo
      </Link>
    </div>
    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
      <div>
        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
          <template v-for="(link, index) in links" :key="index">
            <Link
              v-if="link.url"
              :href="link.url"
              class="relative inline-flex items-center px-4 py-2 text-sm font-medium focus:z-20"
              :class="{
                'bg-indigo-50 border-indigo-500 text-indigo-600 z-10': link.active,
                'bg-white border-gray-300 text-gray-500 hover:bg-gray-50': !link.active,
                'rounded-l-md': index === 0,
                'rounded-r-md': index === links.length - 1,
              }"
              v-html="link.label"
            />
            <span
              v-else
              class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-300 bg-white border border-gray-300 cursor-default"
              :class="{
                'rounded-l-md': index === 0,
                'rounded-r-md': index === links.length - 1,
              }"
              v-html="link.label"
            />
          </template>
        </nav>
      </div>
    </div>
  </nav>
</template>
