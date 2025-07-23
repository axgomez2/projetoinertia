<template>
  <AdminLayout>
    <div class="p-6">
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Relatório de Vinis</h1>
        <p class="text-gray-600">Análise de vendas e inventário de vinis</p>
      </div>

      <!-- Date Filter -->
      <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form @submit.prevent="applyFilters" class="flex flex-wrap gap-4 items-end">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Data Inicial</label>
            <input
              v-model="filters.start_date"
              type="date"
              class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Data Final</label>
            <input
              v-model="filters.end_date"
              type="date"
              class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
            <input
              v-model="filters.category"
              type="text"
              placeholder="Filtrar por categoria"
              class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Artista</label>
            <input
              v-model="filters.artist"
              type="text"
              placeholder="Filtrar por artista"
              class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <button
            type="submit"
            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors"
          >
            Aplicar Filtros
          </button>
          <button
            type="button"
            @click="exportData"
            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors"
          >
            Exportar
          </button>
        </form>
      </div>

      <!-- Sales Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-2 bg-blue-100 rounded-lg">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M8 11v6h8v-6M8 11H6a2 2 0 00-2 2v6a2 2 0 002 2h12a2 2 0 002-2v-6a2 2 0 00-2-2h-2"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Vendas Online</p>
              <p class="text-2xl font-bold text-gray-900">{{ salesData.online.quantity }}</p>
              <p class="text-sm text-gray-500">R$ {{ formatCurrency(salesData.online.revenue) }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-2 bg-purple-100 rounded-lg">
              <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Vendas PDV</p>
              <p class="text-2xl font-bold text-gray-900">{{ salesData.pos.quantity }}</p>
              <p class="text-sm text-gray-500">R$ {{ formatCurrency(salesData.pos.revenue) }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-2 bg-green-100 rounded-lg">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Total Vendido</p>
              <p class="text-2xl font-bold text-gray-900">{{ salesData.total.quantity }}</p>
              <p class="text-sm text-gray-500">R$ {{ formatCurrency(salesData.total.revenue) }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-2 bg-yellow-100 rounded-lg">
              <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Pedidos Online</p>
              <p class="text-2xl font-bold text-gray-900">{{ salesData.online.orders }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Top Selling Vinyls -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow">
          <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Top Vendas Online</h3>
          </div>
          <div class="p-6">
            <div v-if="topSellingVinyls.online.length === 0" class="text-center text-gray-500 py-8">
              Nenhum dado encontrado para o período selecionado
            </div>
            <div v-else class="space-y-4">
              <div
                v-for="vinyl in topSellingVinyls.online"
                :key="vinyl.title"
                class="flex items-center space-x-4"
              >
                <img
                  :src="vinyl.cover_image || '/images/vinyl-placeholder.png'"
                  :alt="vinyl.title"
                  class="w-12 h-12 rounded object-cover"
                />
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900 truncate">{{ vinyl.title }}</p>
                  <p class="text-sm text-gray-500">{{ vinyl.total_sold }} vendidos</p>
                </div>
                <div class="text-right">
                  <p class="text-sm font-medium text-gray-900">R$ {{ formatCurrency(vinyl.total_revenue) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow">
          <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Top Vendas PDV</h3>
          </div>
          <div class="p-6">
            <div v-if="topSellingVinyls.pos.length === 0" class="text-center text-gray-500 py-8">
              Nenhum dado encontrado para o período selecionado
            </div>
            <div v-else class="space-y-4">
              <div
                v-for="vinyl in topSellingVinyls.pos"
                :key="vinyl.title"
                class="flex items-center space-x-4"
              >
                <img
                  :src="vinyl.cover_image || '/images/vinyl-placeholder.png'"
                  :alt="vinyl.title"
                  class="w-12 h-12 rounded object-cover"
                />
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900 truncate">{{ vinyl.title }}</p>
                  <p class="text-sm text-gray-500">{{ vinyl.total_sold }} vendidos</p>
                </div>
                <div class="text-right">
                  <p class="text-sm font-medium text-gray-900">R$ {{ formatCurrency(vinyl.total_revenue) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Inventory Status -->
      <div class="bg-white rounded-lg shadow mb-8">
        <div class="p-6 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Status do Inventário</h3>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div v-for="(items, status) in inventoryData" :key="status" class="space-y-3">
              <h4 class="font-medium text-gray-900 capitalize">
                {{ getInventoryStatusLabel(status) }}
                <span class="text-sm text-gray-500">({{ items.length }})</span>
              </h4>
              <div class="space-y-2 max-h-64 overflow-y-auto">
                <div
                  v-for="item in items.slice(0, 10)"
                  :key="item.id"
                  class="flex items-center justify-between p-2 bg-gray-50 rounded"
                >
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">
                      {{ item.vinyl_master?.title || 'Título não disponível' }}
                    </p>
                  </div>
                  <span class="text-sm text-gray-500">{{ item.stock }}</span>
                </div>
                <div v-if="items.length > 10" class="text-sm text-gray-500 text-center">
                  +{{ items.length - 10 }} mais...
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { router } from '@inertiajs/vue3';
import { ref, reactive } from 'vue';

interface VinylSalesData {
  online: {
    quantity: number;
    revenue: number;
    orders: number;
  };
  pos: {
    quantity: number;
    revenue: number;
    sales: number;
  };
  total: {
    quantity: number;
    revenue: number;
  };
}

interface TopSellingVinyl {
  title: string;
  cover_image: string | null;
  total_sold: number;
  total_revenue: number;
  source: string;
}

interface InventoryItem {
  id: number;
  stock: number;
  vinyl_master?: {
    title: string;
  };
}

interface Props {
  salesData: VinylSalesData;
  inventoryData: Record<string, InventoryItem[]>;
  topSellingVinyls: {
    online: TopSellingVinyl[];
    pos: TopSellingVinyl[];
  };
  salesTrends: any;
  dateRange: [string, string];
  filters: {
    start_date?: string;
    end_date?: string;
    category?: string;
    artist?: string;
  };
}

const props = defineProps<Props>();

const filters = reactive({
  start_date: props.filters.start_date || '',
  end_date: props.filters.end_date || '',
  category: props.filters.category || '',
  artist: props.filters.artist || ''
});

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value);
};

const getInventoryStatusLabel = (status: string) => {
  const labels: Record<string, string> = {
    out_of_stock: 'Sem Estoque',
    low_stock: 'Estoque Baixo',
    in_stock: 'Em Estoque'
  };
  return labels[status] || status;
};

const applyFilters = () => {
  router.get(route('admin.reports.vinyls'), filters, {
    preserveState: true,
    preserveScroll: true
  });
};

const exportData = () => {
  const params = new URLSearchParams(filters as any);
  params.append('export', 'true');
  window.open(`${route('admin.reports.vinyls')}?${params.toString()}`, '_blank');
};
</script>
