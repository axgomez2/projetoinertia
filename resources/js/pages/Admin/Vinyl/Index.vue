<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import Pagination from '@/components/Pagination.vue';
import debounce from 'lodash/debounce';

defineOptions({
  layout: AdminLayout,
});

const props = defineProps({
  vinyls: Object,
  filters: Object,
  filterOptions: Object,
});

// Estados para filtros básicos
const search = ref(props.filters?.search || '');
const artist = ref(props.filters?.artist || '');
const category = ref(props.filters?.category || '');
const status = ref(props.filters?.status || '');

// Estados para filtros avançados
const year = ref(props.filters?.year || '');
const yearFrom = ref(props.filters?.year_from || '');
const yearTo = ref(props.filters?.year_to || '');
const mediaStatus = ref(props.filters?.media_status || '');
const coverStatus = ref(props.filters?.cover_status || '');
const priceFrom = ref(props.filters?.price_from || '');
const priceTo = ref(props.filters?.price_to || '');
const country = ref(props.filters?.country || '');

// Estados para ordenação e paginação
const sortBy = ref(props.filters?.sort_by || 'created_at');
const sortDirection = ref(props.filters?.sort_direction || 'desc');
const perPage = ref(props.filters?.per_page || 15);

// Estados para interface
const showAdvancedFilters = ref(false);
const selectedVinyls = ref([]);
const bulkAction = ref('');
const showBulkActions = ref(false);
const isLoading = ref(false);

// Computed para verificar se há filtros ativos
const hasActiveFilters = computed(() => {
  return search.value || artist.value || category.value || status.value ||
         year.value || yearFrom.value || yearTo.value || mediaStatus.value ||
         coverStatus.value || priceFrom.value || priceTo.value || country.value;
});

// Observar mudanças nos filtros e atualizar a página
watch([
  search, artist, category, status, year, yearFrom, yearTo,
  mediaStatus, coverStatus, priceFrom, priceTo, country,
  sortBy, sortDirection, perPage
], debounce(function () {
  isLoading.value = true;
  router.get(route('admin.vinyls.index'), {
    search: search.value,
    artist: artist.value,
    category: category.value,
    status: status.value,
    year: year.value,
    year_from: yearFrom.value,
    year_to: yearTo.value,
    media_status: mediaStatus.value,
    cover_status: coverStatus.value,
    price_from: priceFrom.value,
    price_to: priceTo.value,
    country: country.value,
    sort_by: sortBy.value,
    sort_direction: sortDirection.value,
    per_page: perPage.value,
  }, {
    preserveState: true,
    replace: true,
    onFinish: () => {
      isLoading.value = false;
    }
  });
}, 300));

// Função para limpar todos os filtros
const clearFilters = () => {
  search.value = '';
  artist.value = '';
  category.value = '';
  status.value = '';
  year.value = '';
  yearFrom.value = '';
  yearTo.value = '';
  mediaStatus.value = '';
  coverStatus.value = '';
  priceFrom.value = '';
  priceTo.value = '';
  country.value = '';
};

// Função para alternar direção da ordenação
const toggleSort = (column) => {
  if (sortBy.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortBy.value = column;
    sortDirection.value = 'asc';
  }
};

// Função para excluir um disco
const deleteVinyl = (id) => {
  if (confirm('Tem certeza que deseja excluir este disco? Esta ação não pode ser desfeita.')) {
    router.delete(route('admin.vinyls.destroy', id));
  }
};

// Funções para seleção em lote
const toggleVinylSelection = (vinylId) => {
  const index = selectedVinyls.value.indexOf(vinylId);
  if (index > -1) {
    selectedVinyls.value.splice(index, 1);
  } else {
    selectedVinyls.value.push(vinylId);
  }
  showBulkActions.value = selectedVinyls.value.length > 0;
};

const selectAllVinyls = () => {
  if (selectedVinyls.value.length === props.vinyls.data.length) {
    selectedVinyls.value = [];
    showBulkActions.value = false;
  } else {
    selectedVinyls.value = props.vinyls.data.map(vinyl => vinyl.id);
    showBulkActions.value = true;
  }
};

const executeBulkAction = () => {
  if (!bulkAction.value || selectedVinyls.value.length === 0) return;

  if (bulkAction.value === 'delete') {
    if (!confirm(`Tem certeza que deseja excluir ${selectedVinyls.value.length} disco(s)? Esta ação não pode ser desfeita.`)) {
      return;
    }
  }

  router.post(route('admin.vinyls.bulkAction'), {
    action: bulkAction.value,
    vinyl_ids: selectedVinyls.value,
  });

  selectedVinyls.value = [];
  showBulkActions.value = false;
  bulkAction.value = '';
};

// Função para exportar dados
const exportData = (format = 'csv') => {
  const params = new URLSearchParams({
    format,
    search: search.value,
    artist: artist.value,
    category: category.value,
    status: status.value,
  });

  window.open(route('admin.vinyls.export') + '?' + params.toString());
};

// Função para determinar a classe CSS do status
const getStatusClass = (vinyl) => {
  if (!vinyl.vinyl_sec) {
    return 'bg-yellow-100 text-yellow-800'; // Incompleto
  }
  if (vinyl.is_promotional) {
    return 'bg-purple-100 text-purple-800'; // Promocional
  }
  if (vinyl.in_stock && vinyl.stock > 0) {
    return 'bg-green-100 text-green-800'; // Em estoque
  }
  return 'bg-red-100 text-red-800'; // Fora de estoque
};

// Função para obter o texto do status
const getStatusText = (vinyl) => {
  if (!vinyl.vinyl_sec) {
    return 'Incompleto';
  }
  if (vinyl.is_promotional) {
    return 'Promocional';
  }
  if (vinyl.in_stock && vinyl.stock > 0) {
    return `Em Estoque (${vinyl.stock})`;
  }
  return 'Fora de Estoque';
};

// Função para obter URL da imagem otimizada
const getOptimizedImageUrl = (vinyl) => {
  if (!vinyl.cover_image) {
    return '/images/vinyl-placeholder.svg';
  }

  // Se for URL externa (Discogs), retorna como está
  if (vinyl.cover_image.startsWith('http')) {
    return vinyl.cover_image;
  }

  // Para imagens locais, garante o caminho correto
  const imagePath = vinyl.cover_image.startsWith('/') ? vinyl.cover_image : '/' + vinyl.cover_image;

  // Adiciona timestamp para evitar cache de imagens antigas
  return imagePath + '?v=' + Date.now();
};

// Estado para controle de imagens expandidas
const expandedImages = ref(new Set());

// Função para alternar expansão da imagem
const toggleImageExpansion = (vinylId) => {
  if (expandedImages.value.has(vinylId)) {
    expandedImages.value.delete(vinylId);
  } else {
    expandedImages.value.add(vinylId);
  }
};

// Função para verificar se imagem está expandida
const isImageExpanded = (vinylId) => {
  return expandedImages.value.has(vinylId);
};

// Função para lidar com erro de carregamento de imagem
const handleImageError = (event) => {
  event.target.src = '/images/vinyl-placeholder.svg';
  event.target.classList.add('opacity-50');
};

// Função para formatar preço
const formatPrice = (vinyl) => {
  if (!vinyl.price) return '-';

  const price = parseFloat(vinyl.price);
  if (vinyl.is_promotional && vinyl.promotional_price) {
    const promoPrice = parseFloat(vinyl.promotional_price);
    return `R$ ${promoPrice.toFixed(2).replace('.', ',')} (era R$ ${price.toFixed(2).replace('.', ',')})`;
  }

  return `R$ ${price.toFixed(2).replace('.', ',')}`;
};
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl text-gray-700 font-bold">Discos de Vinil</h1>
        <div class="flex items-center space-x-4 mt-2">
          <p class="text-sm text-gray-500">
            {{ vinyls.total }} disco(s) encontrado(s)
          </p>
          <div class="flex items-center space-x-3 text-xs">
            <span class="inline-flex items-center px-2 py-1 rounded-full bg-green-100 text-green-800">
              <svg class="w-2 h-2 mr-1" fill="currentColor" viewBox="0 0 8 8">
                <circle cx="4" cy="4" r="3"/>
              </svg>
              {{ vinyls.data.filter(v => v.in_stock && v.stock > 0).length }} em estoque
            </span>
            <span class="inline-flex items-center px-2 py-1 rounded-full bg-yellow-100 text-yellow-800">
              <svg class="w-2 h-2 mr-1" fill="currentColor" viewBox="0 0 8 8">
                <circle cx="4" cy="4" r="3"/>
              </svg>
              {{ vinyls.data.filter(v => !v.vinyl_sec).length }} incompletos
            </span>
            <span class="inline-flex items-center px-2 py-1 rounded-full bg-purple-100 text-purple-800">
              <svg class="w-2 h-2 mr-1" fill="currentColor" viewBox="0 0 8 8">
                <circle cx="4" cy="4" r="3"/>
              </svg>
              {{ vinyls.data.filter(v => v.is_promotional).length }} promocionais
            </span>
          </div>
        </div>
      </div>
      <div class="flex space-x-3">
        <button
          @click="exportData('csv')"
          class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
        >
          Exportar CSV
        </button>
        <Link
          :href="route('admin.vinyls.create')"
          class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition"
        >
          Adicionar Novo Disco
        </Link>
      </div>
    </div>

    <!-- Filtros Básicos -->
    <div class="bg-white p-4 rounded-lg shadow-md mb-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="relative">
          <input
            v-model="search"
            type="text"
            placeholder="Buscar por título, artista, gravadora, Discogs ID..."
            class="sm:text-sm rounded-md text-gray-700 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 pl-10"
          >
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </div>
          <div v-if="search" class="absolute inset-y-0 right-0 pr-3 flex items-center">
            <button @click="search = ''" class="text-gray-400 hover:text-gray-600">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
        </div>
        <div class="relative">
          <input
            v-model="artist"
            type="text"
            placeholder="Filtrar por artista..."
            class="sm:text-sm rounded-md text-gray-700 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 pl-8"
          >
          <div class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none">
            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
          </div>
          <div v-if="artist" class="absolute inset-y-0 right-0 pr-3 flex items-center">
            <button @click="artist = ''" class="text-gray-400 hover:text-gray-600">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
        </div>
        <select
          v-model="category"
          class="sm:text-sm rounded-md text-gray-700 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
        >
          <option value="">Todas as Categorias</option>
          <option v-for="cat in filterOptions?.categories || []" :key="cat.id" :value="cat.id">
            {{ cat.name }}
          </option>
        </select>
        <select
          v-model="status"
          class="sm:text-sm rounded-md text-gray-700 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
        >
          <option value="">Todos os Status</option>
          <option value="available">✅ Disponível</option>
          <option value="unavailable">❌ Indisponível</option>
          <option value="incomplete">⚠️ Incompleto</option>
          <option value="promotional">🎯 Promocional</option>
        </select>
      </div>

      <!-- Botões de controle -->
      <div class="flex justify-between items-center">
        <div class="flex space-x-2">
          <button
            @click="showAdvancedFilters = !showAdvancedFilters"
            class="text-sm text-gray-600 hover:text-gray-800"
          >
            {{ showAdvancedFilters ? 'Ocultar' : 'Mostrar' }} Filtros Avançados
          </button>
          <button
            v-if="hasActiveFilters"
            @click="clearFilters"
            class="text-sm text-red-600 hover:text-red-800"
          >
            Limpar Filtros
          </button>
        </div>

        <div class="flex items-center space-x-4">
          <div class="flex items-center space-x-2">
            <label class="text-sm text-gray-600">Ordenar por:</label>
            <select v-model="sortBy" class="text-sm border-gray-300 rounded focus:border-indigo-500 focus:ring-indigo-500">
              <option value="created_at">Data de Criação</option>
              <option value="title">Título</option>
              <option value="release_year">Ano de Lançamento</option>
              <option value="price">Preço</option>
              <option value="stock">Estoque</option>
            </select>
            <button
              @click="sortDirection = sortDirection === 'asc' ? 'desc' : 'asc'"
              class="p-1 text-gray-500 hover:text-gray-700 transition-colors"
              :title="sortDirection === 'asc' ? 'Crescente' : 'Decrescente'"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path v-if="sortDirection === 'asc'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path>
                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4"></path>
              </svg>
            </button>
          </div>

          <label class="text-sm text-gray-600">
            Itens por página:
            <select v-model="perPage" class="ml-1 text-sm border-gray-300 rounded focus:border-indigo-500 focus:ring-indigo-500">
              <option value="10">10</option>
              <option value="15">15</option>
              <option value="25">25</option>
              <option value="50">50</option>
              <option value="100">100</option>
            </select>
          </label>
        </div>
      </div>
    </div>

    <!-- Filtros Avançados -->
    <div v-if="showAdvancedFilters" class="bg-white p-4 rounded-lg shadow-md mb-4">
      <h3 class="text-lg font-medium text-gray-700 mb-4 flex items-center">
        <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
        </svg>
        Filtros Avançados
      </h3>

      <!-- Seção de Ano -->
      <div class="mb-6">
        <h4 class="text-sm font-semibold text-gray-600 mb-3 uppercase tracking-wide">Período de Lançamento</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ano Específico</label>
            <select v-model="year" class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
              <option value="">Todos os anos</option>
              <option v-for="yearOption in filterOptions?.years || []" :key="yearOption" :value="yearOption">
                {{ yearOption }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ano de</label>
            <input v-model="yearFrom" type="number" min="1900" :max="new Date().getFullYear() + 5"
                   class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                   placeholder="1970">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ano até</label>
            <input v-model="yearTo" type="number" min="1900" :max="new Date().getFullYear() + 5"
                   class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                   placeholder="2024">
          </div>
        </div>
      </div>

      <!-- Seção de Localização e Gênero -->
      <div class="mb-6">
        <h4 class="text-sm font-semibold text-gray-600 mb-3 uppercase tracking-wide">Localização e Gênero</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">País de Origem</label>
            <select v-model="country" class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
              <option value="">Todos os países</option>
              <option v-for="countryOption in filterOptions?.countries || []" :key="countryOption" :value="countryOption">
                {{ countryOption }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Gênero Musical</label>
            <select v-model="category" class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
              <option value="">Todos os gêneros</option>
              <option v-for="cat in filterOptions?.categories || []" :key="cat.id" :value="cat.id">
                {{ cat.name }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <!-- Seção de Condição -->
      <div class="mb-6">
        <h4 class="text-sm font-semibold text-gray-600 mb-3 uppercase tracking-wide">Condição do Produto</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Condição da Mídia</label>
            <select v-model="mediaStatus" class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
              <option value="">Todas as condições</option>
              <option v-for="status in filterOptions?.mediaStatuses || []" :key="status.id" :value="status.id">
                {{ status.title }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Condição da Capa</label>
            <select v-model="coverStatus" class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
              <option value="">Todas as condições</option>
              <option v-for="status in filterOptions?.coverStatuses || []" :key="status.id" :value="status.id">
                {{ status.title }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <!-- Seção de Preço -->
      <div class="mb-4">
        <h4 class="text-sm font-semibold text-gray-600 mb-3 uppercase tracking-wide">Faixa de Preço</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Preço mínimo (R$)</label>
            <input v-model="priceFrom" type="number" step="0.01" min="0"
                   class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                   placeholder="0,00">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Preço máximo (R$)</label>
            <input v-model="priceTo" type="number" step="0.01" min="0"
                   class="w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                   placeholder="999,99">
          </div>
        </div>
      </div>
    </div>

    <!-- Ações em Lote -->
    <div v-if="showBulkActions" class="bg-blue-50 p-4 rounded-lg shadow-md mb-4">
      <div class="flex items-center justify-between">
        <span class="text-sm text-blue-700">
          {{ selectedVinyls.length }} disco(s) selecionado(s)
        </span>
        <div class="flex items-center space-x-3">
          <select v-model="bulkAction" class="text-sm border-gray-300 rounded">
            <option value="">Selecione uma ação</option>
            <option value="delete">Excluir selecionados</option>
            <option value="update_status">Atualizar status</option>
            <option value="update_category">Atualizar categoria</option>
          </select>
          <button
            @click="executeBulkAction"
            :disabled="!bulkAction"
            class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 disabled:opacity-50"
          >
            Executar
          </button>
        </div>
      </div>
    </div>

    <!-- Tabela de Discos -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th scope="col" class="px-6 py-3 text-left">
              <input
                type="checkbox"
                @change="selectAllVinyls"
                :checked="selectedVinyls.length === vinyls.data.length && vinyls.data.length > 0"
                class="rounded border-gray-300"
              >
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Capa
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors"
                @click="toggleSort('title')">
              <div class="flex items-center space-x-1">
                <span>Título</span>
                <svg v-if="sortBy === 'title'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path v-if="sortDirection === 'asc'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                  <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
                <svg v-else class="w-4 h-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                </svg>
              </div>
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Artistas
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors"
                @click="toggleSort('release_year')">
              <div class="flex items-center space-x-1">
                <span>Ano</span>
                <svg v-if="sortBy === 'release_year'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path v-if="sortDirection === 'asc'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                  <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
                <svg v-else class="w-4 h-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                </svg>
              </div>
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors"
                @click="toggleSort('price')">
              <div class="flex items-center space-x-1">
                <span>Preço</span>
                <svg v-if="sortBy === 'price'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path v-if="sortDirection === 'asc'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                  <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
                <svg v-else class="w-4 h-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                </svg>
              </div>
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors"
                @click="toggleSort('stock')">
              <div class="flex items-center space-x-1">
                <span>Estoque</span>
                <svg v-if="sortBy === 'stock'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path v-if="sortDirection === 'asc'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                  <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
                <svg v-else class="w-4 h-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                </svg>
              </div>
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Status
            </th>
            <th scope="col" class="relative px-6 py-3">
              <span class="sr-only">Ações</span>
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="vinyl in vinyls.data" :key="vinyl.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">
              <input
                type="checkbox"
                :checked="selectedVinyls.includes(vinyl.id)"
                @change="toggleVinylSelection(vinyl.id)"
                class="rounded border-gray-300"
              >
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="relative group">
                <img
                  :src="getOptimizedImageUrl(vinyl)"
                  :alt="`Capa de ${vinyl.title}`"
                  :class="[
                    'object-cover rounded-lg shadow-sm cursor-pointer transition-all duration-300',
                    isImageExpanded(vinyl.id)
                      ? 'w-32 h-32 z-10 absolute top-0 left-0 shadow-xl border-2 border-gray-300'
                      : 'w-16 h-16 hover:shadow-md'
                  ]"
                  @error="handleImageError"
                  @click="toggleImageExpansion(vinyl.id)"
                  loading="lazy"
                >
                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 rounded-lg transition-all pointer-events-none"></div>

                <!-- Indicador de zoom -->
                <div class="absolute bottom-1 right-1 bg-black bg-opacity-50 text-white text-xs px-1 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                  </svg>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 text-sm font-medium text-gray-700">
              <div class="max-w-xs">
                <div class="font-semibold truncate mb-1" :title="vinyl.title">{{ vinyl.title }}</div>
                <div class="flex items-center space-x-2 text-xs text-gray-500">
                  <span v-if="vinyl.country" class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100">
                    {{ vinyl.country }}
                  </span>
                  <span v-if="vinyl.discogs_id" class="text-gray-400">
                    #{{ vinyl.discogs_id }}
                  </span>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 text-sm text-gray-700">
              <div class="max-w-xs">
                <div v-if="vinyl.artists && vinyl.artists.length > 0" class="space-y-1">
                  <div v-for="(artist, index) in vinyl.artists.slice(0, 2)" :key="artist.id"
                       class="truncate" :title="artist.name">
                    {{ artist.name }}
                  </div>
                  <div v-if="vinyl.artists.length > 2" class="text-xs text-gray-400">
                    +{{ vinyl.artists.length - 2 }} mais
                  </div>
                </div>
                <span v-else class="text-gray-400 italic">Sem artista</span>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ vinyl.release_year || '-' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              <div v-html="formatPrice(vinyl)"></div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ vinyl.stock || 0 }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <div class="flex flex-col space-y-1">
                <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full" :class="getStatusClass(vinyl)">
                  <svg class="w-2 h-2 mr-1" fill="currentColor" viewBox="0 0 8 8">
                    <circle cx="4" cy="4" r="3"/>
                  </svg>
                  {{ getStatusText(vinyl) }}
                </span>

                <!-- Indicadores adicionais -->
                <div class="flex space-x-1">
                  <span v-if="vinyl.is_promotional" class="inline-flex items-center px-1.5 py-0.5 text-xs font-medium bg-purple-100 text-purple-800 rounded">
                    PROMO
                  </span>
                  <span v-if="vinyl.is_presale" class="inline-flex items-center px-1.5 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 rounded">
                    PRÉ-VENDA
                  </span>
                  <span v-if="!vinyl.vinyl_sec" class="inline-flex items-center px-1.5 py-0.5 text-xs font-medium bg-yellow-100 text-yellow-800 rounded">
                    INCOMPLETO
                  </span>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <div class="flex space-x-2">
                <Link
                  :href="route('admin.vinyls.show', vinyl.id)"
                  class="text-blue-600 hover:text-blue-900"
                  title="Ver detalhes"
                >
                  Ver
                </Link>
                <div v-if="!vinyl.vinyl_sec">
                  <Link
                    :href="route('admin.vinyls.complete', vinyl.id)"
                    class="text-yellow-600 hover:text-yellow-900 font-bold"
                    title="Completar cadastro"
                  >
                    Completar
                  </Link>
                </div>
                <div v-else class="flex space-x-2">
                  <Link
                    :href="route('admin.vinyls.edit', vinyl.id)"
                    class="text-indigo-600 hover:text-indigo-900"
                    title="Editar"
                  >
                    Editar
                  </Link>
                  <button
                    @click="deleteVinyl(vinyl.id)"
                    class="text-red-600 hover:text-red-900"
                    title="Excluir"
                  >
                    Excluir
                  </button>
                </div>
              </div>
            </td>
          </tr>
          <tr v-if="vinyls.data.length === 0 && !isLoading">
            <td colspan="9" class="px-6 py-12 text-center">
              <div class="text-gray-500">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-gray-100 mb-4">
                  <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-.895 2-2 2s-2-.895-2-2 .895-2 2-2 2 .895 2 2zm12-3c0 1.105-.895 2-2 2s-2-.895-2-2 .895-2 2-2 2 .895 2 2zM9 10l12-3" />
                  </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">
                  {{ hasActiveFilters ? 'Nenhum disco encontrado' : 'Nenhum disco cadastrado' }}
                </h3>
                <p class="text-sm text-gray-500 mb-6 max-w-sm mx-auto">
                  {{ hasActiveFilters
                    ? 'Tente ajustar os filtros de busca ou limpar todos os filtros para ver mais resultados.'
                    : 'Comece adicionando seu primeiro disco de vinil ao catálogo da loja.'
                  }}
                </p>
                <div class="flex justify-center space-x-3">
                  <button
                    v-if="hasActiveFilters"
                    @click="clearFilters"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Limpar Filtros
                  </button>
                  <Link
                    :href="route('admin.vinyls.create')"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Adicionar Disco
                  </Link>
                </div>
              </div>
            </td>
          </tr>

          <!-- Loading state -->
          <tr v-if="isLoading">
            <td colspan="9" class="px-6 py-8 text-center">
              <div class="flex items-center justify-center">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-gray-500">Carregando discos...</span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Paginação -->
    <div v-if="vinyls.links && vinyls.links.length > 3" class="mt-6">
      <Pagination :links="vinyls.links" />
    </div>
  </div>
</template>
