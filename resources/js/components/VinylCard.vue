<template>
  <div class="group relative bg-white border border-gray-200 rounded-lg shadow hover:shadow-md transition-shadow duration-200">
    <!-- Vinyl Cover Image -->
    <div class="w-full h-48 overflow-hidden rounded-t-lg bg-gray-200 relative">
      <img
        :src="imageUrl"
        :alt="vinyl.title"
        class="h-full w-full object-cover object-center group-hover:opacity-75 transition-opacity duration-200"
        @error="handleImageError"
        @load="handleImageLoad"
      />

      <!-- Loading placeholder -->
      <div
        v-if="imageLoading"
        class="absolute inset-0 flex items-center justify-center bg-gray-100"
      >
        <svg class="animate-spin h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>

      <!-- Status badge -->
      <div v-if="vinyl.status_text" class="absolute top-2 right-2">
        <span
          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
          :class="statusBadgeClass"
        >
          {{ vinyl.status_text }}
        </span>
      </div>
    </div>

    <!-- Vinyl Information -->
    <div class="p-4">

      <!-- Artist -->
      <p class="text-gray-800 text-sm mb-1">
        {{ vinyl.artists || 'Artista não informado' }}
      </p>

      <!-- Title -->
      <h3 class="font-medium text-gray-700 text-sm mb-1 group-hover:text-indigo-600 transition-colors">
        {{ vinyl.title }}
      </h3>

      <!-- Price and Actions -->
      <div class="flex items-center justify-between">
        <div class="flex flex-col">
          <span v-if="vinyl.current_price" class="font-semibold text-gray-900">
            R$ {{ formatPrice(vinyl.current_price) }}
          </span>
          <span v-else class="text-sm text-gray-500">
            Preço não informado
          </span>
        </div>

        <!-- Action button slot -->
        <slot name="actions" :vinyl="vinyl">
          <button
            v-if="showViewButton"
            class="text-indigo-600 hover:text-indigo-800 text-sm font-medium transition-colors"
            @click="$emit('view', vinyl)"
          >
            Ver Detalhes
          </button>
        </slot>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import type { Vinyl } from '@/types';

interface Props {
  vinyl: Vinyl;
  showViewButton?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  showViewButton: false
});

const emit = defineEmits<{
  view: [vinyl: Vinyl];
}>();

const imageLoading = ref(true);
const imageError = ref(false);

const imageUrl = computed(() => {
  if (imageError.value || !props.vinyl.cover_image) {
    return '/images/vinyl-placeholder.svg';
  }
  return props.vinyl.cover_image;
});

const statusBadgeClass = computed(() => {
  const status = props.vinyl.status_text?.toLowerCase();

  if (status?.includes('estoque')) {
    return 'bg-green-100 text-green-800';
  } else if (status?.includes('promocional')) {
    return 'bg-yellow-100 text-yellow-800';
  } else if (status?.includes('fora')) {
    return 'bg-red-100 text-red-800';
  } else {
    return 'bg-gray-100 text-gray-800';
  }
});

const formatPrice = (price: number): string => {
  return parseFloat(price.toString()).toFixed(2).replace('.', ',');
};

const handleImageError = (): void => {
  imageError.value = true;
  imageLoading.value = false;
};

const handleImageLoad = (): void => {
  imageLoading.value = false;
  imageError.value = false;
};
</script>

<style scoped>
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
