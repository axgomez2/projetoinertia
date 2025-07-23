<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';

defineOptions({
  layout: AdminLayout,
});

const props = defineProps({
  vinyl: Object,
});

// Função para obter URL da imagem otimizada
const getOptimizedImageUrl = (vinyl) => {
  if (!vinyl.cover_image) {
    return '/images/vinyl-placeholder.svg';
  }

  if (vinyl.cover_image.startsWith('http')) {
    return vinyl.cover_image;
  }

  return vinyl.cover_image.startsWith('/') ? vinyl.cover_image : '/' + vinyl.cover_image;
};

// Função para formatar preço
const formatPrice = (price) => {
  if (!price) return '-';
  return `R$ ${parseFloat(price).toFixed(2).replace('.', ',')}`;
};

// Função para excluir disco
const deleteVinyl = () => {
  if (confirm('Tem certeza que deseja excluir este disco? Esta ação não pode ser desfeita.')) {
    router.delete(route('admin.vinyls.destroy', props.vinyl.id));
  }
};
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl text-gray-700 font-bold">{{ vinyl.title }}</h1>
        <p class="text-sm text-gray-500 mt-1">
          Detalhes do disco de vinil
        </p>
      </div>
      <div class="flex space-x-3">
        <Link
          :href="route('admin.vinyls.index')"
          class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
        >
          ← Voltar
        </Link>
        <Link
          v-if="vinyl.vinyl_sec"
          :href="route('admin.vinyls.edit', vinyl.id)"
          class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
        >
          Editar
        </Link>
        <button
          @click="deleteVinyl"
          class="inline-flex items-center px-3 py-2 border border-red-300 rounded-md text-sm font-medium text-red-700 bg-white hover:bg-red-50"
        >
          Excluir
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Imagem da Capa -->
      <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-md p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Capa do Álbum</h3>
          <div class="flex justify-center">
            <img
              :src="getOptimizedImageUrl(vinyl)"
              :alt="`Capa de ${vinyl.title}`"
              class="w-full max-w-sm rounded-lg shadow-lg"
              @error="$event.target.src = '/images/vinyl-placeholder.svg'"
            >
          </div>
        </div>
      </div>

      <!-- Informações Principais -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Informações Básicas -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Informações Básicas</h3>
          <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <dt class="text-sm font-medium text-gray-500">Título</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ vinyl.title }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Ano de Lançamento</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ vinyl.release_year || '-' }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">País</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ vinyl.country || '-' }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Discogs ID</dt>
              <dd class="mt-1 text-sm text-gray-900">
                <a v-if="vinyl.discogs_url" :href="vinyl.discogs_url" target="_blank" class="text-blue-600 hover:text-blue-800">
                  {{ vinyl.discogs_id }}
                </a>
                <span v-else>{{ vinyl.discogs_id }}</span>
              </dd>
            </div>
            <div v-if="vinyl.description" class="md:col-span-2">
              <dt class="text-sm font-medium text-gray-500">Descrição</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ vinyl.description }}</dd>
            </div>
          </dl>
        </div>

        <!-- Artistas -->
        <div v-if="vinyl.artists && vinyl.artists.length > 0" class="bg-white rounded-lg shadow-md p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Artistas</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-for="artist in vinyl.artists" :key="artist.id" class="flex items-center space-x-3">
              <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                  <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                  </svg>
                </div>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-900">{{ artist.name }}</p>
                <p v-if="artist.discogs_url" class="text-sm text-gray-500">
                  <a :href="artist.discogs_url" target="_blank" class="text-blue-600 hover:text-blue-800">
                    Ver no Discogs
                  </a>
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Gravadoras -->
        <div v-if="vinyl.record_labels && vinyl.record_labels.length > 0" class="bg-white rounded-lg shadow-md p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Gravadoras</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-for="label in vinyl.record_labels" :key="label.id" class="flex items-center space-x-3">
              <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                  <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
                  </svg>
                </div>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-900">{{ label.name }}</p>
                <p v-if="label.discogs_url" class="text-sm text-gray-500">
                  <a :href="label.discogs_url" target="_blank" class="text-blue-600 hover:text-blue-800">
                    Ver no Discogs
                  </a>
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Informações de Estoque e Preço -->
        <div v-if="vinyl.vinyl_sec" class="bg-white rounded-lg shadow-md p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Estoque e Preço</h3>
          <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <dt class="text-sm font-medium text-gray-500">Preço</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ formatPrice(vinyl.vinyl_sec.price) }}</dd>
            </div>
            <div v-if="vinyl.vinyl_sec.promotional_price">
              <dt class="text-sm font-medium text-gray-500">Preço Promocional</dt>
              <dd class="mt-1 text-sm text-green-600 font-medium">{{ formatPrice(vinyl.vinyl_sec.promotional_price) }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Estoque</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ vinyl.vinyl_sec.stock || 0 }} unidades</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Status da Mídia</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ vinyl.vinyl_sec.midia_status?.title || '-' }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Status da Capa</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ vinyl.vinyl_sec.cover_status?.title || '-' }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Fornecedor</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ vinyl.vinyl_sec.supplier?.name || '-' }}</dd>
            </div>
          </dl>
        </div>

        <!-- Categorias -->
        <div v-if="vinyl.categories && vinyl.categories.length > 0" class="bg-white rounded-lg shadow-md p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Categorias</h3>
          <div class="flex flex-wrap gap-2">
            <span
              v-for="category in vinyl.categories"
              :key="category.id"
              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
            >
              {{ category.name }}
            </span>
          </div>
        </div>

        <!-- Faixas -->
        <div v-if="vinyl.tracks && vinyl.tracks.length > 0" class="bg-white rounded-lg shadow-md p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Lista de Faixas</h3>
          <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Posição</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duração</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">YouTube</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="track in vinyl.tracks" :key="track.id">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ track.position }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ track.name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ track.duration || '-' }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <a v-if="track.youtube_url" :href="track.youtube_url" target="_blank" class="text-red-600 hover:text-red-800">
                      Assistir
                    </a>
                    <span v-else>-</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Ação para completar cadastro -->
        <div v-if="!vinyl.vinyl_sec" class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-yellow-800">Cadastro Incompleto</h3>
              <div class="mt-2 text-sm text-yellow-700">
                <p>Este disco ainda não possui informações de estoque e preço. Complete o cadastro para disponibilizá-lo na loja.</p>
              </div>
              <div class="mt-4">
                <Link
                  :href="route('admin.vinyls.complete', vinyl.id)"
                  class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-yellow-800 bg-yellow-100 hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
                >
                  Completar Cadastro
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
