<template>
  <AppHeaderLayout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Hero Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6 text-gray-900">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Bem-vindo à Nossa Loja de Vinis</h1>
                    <p class="text-gray-600">Descubra nossa coleção de vinis completos e prontos para compra</p>
                </div>
            </div>

            <!-- Vinyls Grid Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-6">Vinis Disponíveis</h2>

                    <!-- Loading State -->
                    <div v-if="isLoading" class="text-center py-12">
                        <div class="text-gray-500">
                            <svg class="animate-spin mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">Carregando vinis...</p>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else-if="!vinyls || vinyls.length === 0" class="text-center py-12">
                        <div class="text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-.895 2-2 2s-2-.895-2-2 .895-2 2-2 2 .895 2 2zm12-3c0 1.105-.895 2-2 2s-2-.895-2-2 .895-2 2-2 2 .895 2 2zM9 10l12-3" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum vinil encontrado</h3>
                            <p class="mt-1 text-sm text-gray-500">Não há vinis completos disponíveis no momento.</p>
                        </div>
                    </div>

                    <!-- Vinyls Grid -->
                    <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-6">
                        <VinylCard
                            v-for="vinyl in vinyls"
                            :key="vinyl.id"
                            :vinyl="vinyl"
                            :show-view-button="true"
                            @view="handleViewVinyl"
                        />
                    </div>

                    <!-- View More Link -->
                    <div v-if="vinyls && vinyls.length > 0" class="text-center mt-8">
                        <a
                            href="/vinyls"
                            class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-indigo-600 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200"
                        >
                            Ver Todos os Vinis
                            <svg class="ml-2 -mr-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </AppHeaderLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue';
import VinylCard from '@/components/VinylCard.vue';
import type { Vinyl } from '@/types';

// Props from controller
const props = defineProps<{
    vinyls: Vinyl[];
}>();

// Loading state
const isLoading = ref(false);

// Handle vinyl view action
const handleViewVinyl = (vinyl: Vinyl) => {
    // For now, we'll just log the action
    // In a real application, this would navigate to a vinyl detail page
    console.log('Viewing vinyl:', vinyl);

    // Example: Navigate to vinyl detail page
    // window.location.href = `/vinyls/${vinyl.slug || vinyl.id}`;
};

// Simulate loading state on mount (if needed for future async operations)
onMounted(() => {
    // This could be used for future async data loading
    // For now, data comes from the controller
});
</script>

<style scoped>
/* Custom styles for home page specific adjustments */
.grid {
    /* Ensure consistent spacing across all screen sizes */
    gap: 1.5rem;
}

@media (min-width: 640px) {
    .grid {
        gap: 1.5rem;
    }
}

@media (min-width: 1024px) {
    .grid {
        gap: 2rem;
    }
}

/* Smooth transitions for interactive elements */
a, button {
    transition: all 0.2s ease-in-out;
}

/* Focus styles for accessibility */
a:focus, button:focus {
    outline: 2px solid #4f46e5;
    outline-offset: 2px;
}
</style>
