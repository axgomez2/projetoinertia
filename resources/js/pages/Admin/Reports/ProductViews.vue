<template>
  <AdminLayout>
    <div class="p-6">
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Relatório de Visualizações de Produtos</h1>
        <p class="text-gray-600">Análise de visualizações e conversões de produtos</p>
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

      <!-- Views Stats -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-2 bg-gray-100 rounded-lg">
              <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Total de Visualizações</p>
              <p class="text-2xl font-bold text-gray-900">{{ viewsStats.total_views }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-2 bg-blue-100 rounded-lg">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Visualizações Únicas</p>
              <p class="text-2xl font-bold text-gray-900">{{ viewsStats.unique_views }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-2 bg-purple-100 rounded-lg">
              <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Média de Visualizações por Produto</p>
              <p class="text-2xl font-bold text-gray-900">{{ viewsStats.average_views_per_product }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Conversion Rate -->
      <div class="bg-white rounded-lg shadow mb-8">
        <div class="p-6 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Taxa de Conversão</h3>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-gray-50 p-6 rounded-lg">
              <p class="text-sm font-medium text-gray-600">Taxa de Conversão Geral</p>
              <p class="text-2xl font-bold text-green-600">{{ conversionRates.overall_conversion_rate }}%</p>
            </div>
            <div class="bg-gray-50 p-6 rounded-lg">
              <p class="text-sm font-medium text-gray-600">Total de Visualizações</p>
              <p class="text-2xl font-bold text-gray-900">{{ conversionRates.total_views }}</p>
            </div>
            <div class="bg-gray-50 p-6 rounded-lg">
              <p class="text-sm font-medium text-gray-600">Total de Vendas</p>
              <p class="text-2xl font-bold text-gray-900">{{ conversionRates.total_sales }}</p>
            </div>
            <div class="bg-gray-50 p-6 rounded-lg">
              <p class="text-sm font-medium text-gray-600">Produtos com Conversões</p>
              <p class="text-2xl font-bold text-gray-900">{{ conversionRates.products_with_conversions }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Views Trends -->
      <div class="bg-white rounded-lg shadow mb-8">
        <div class="p-6 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Tendências de Visualizações</h3>
        </div>
        <div class="p-6">
          <div v-if="viewsTrends.length === 0" class="text-center text-gray-500 py-8">
            Nenhum dado encontrado para o período selecionado
          </div>
          <div v-else class="h-80">
            <canvas ref="trendsChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Most Viewed Products -->
      <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Produtos Mais Visualizados</h3>
        </div>
        <div class="p-6">
          <div v-if="mostViewedProducts.length === 0" class="text-center text-gray-500 py-8">
            Nenhum dado encontrado para o período selecionado
          </div>
          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produto</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total de Visualizações</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visualizações Únicas</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preço</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estoque</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="product in mostViewedProducts" :key="product.title">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10">
                        <img class="h-10 w-10 rounded-full object-cover" :src="product.cover_image || '/images/vinyl-placeholder.png'" :alt="product.title">
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">{{ product.title }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ product.total_views }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ product.unique_views }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">R$ {{ formatCurrency(product.price) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="getStockBadgeClass(product.stock)">
                      {{ product.stock }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { router } from '@inertiajs/vue3';
import { reactive, ref, onMounted } from 'vue';
import Chart from 'chart.js/auto';

interface ViewsStats {
  total_views: number;
  unique_views: number;
  average_views_per_product: number;
}

interface ViewedProduct {
  title: string;
  cover_image: string | null;
  total_views: number;
  unique_views: number;
  price: number;
  stock: number;
}

interface ViewsTrend {
  period: string;
  total_views: number;
  unique_views: number;
}

interface ConversionRates {
  overall_conversion_rate: number;
  total_views: number;
  total_sales: number;
  products_with_conversions: number;
}

interface Props {
  viewsStats: ViewsStats;
  mostViewedProducts: ViewedProduct[];
  viewsTrends: ViewsTrend[];
  conversionRates: ConversionRates;
  dateRange: [string, string];
  filters: {
    start_date?: string;
    end_date?: string;
  };
}

const props = defineProps<Props>();
const trendsChart = ref<HTMLCanvasElement | null>(null);
let chart: Chart | null = null;

const filters = reactive({
  start_date: props.filters.start_date || '',
  end_date: props.filters.end_date || ''
});

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value);
};

const getStockBadgeClass = (stock: number) => {
  if (stock === 0) {
    return 'px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800';
  } else if (stock <= 5) {
    return 'px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800';
  } else {
    return 'px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800';
  }
};

const applyFilters = () => {
  router.get(route('admin.reports.product-views'), filters, {
    preserveState: true,
    preserveScroll: true
  });
};

const exportData = () => {
  const params = new URLSearchParams(filters as any);
  params.append('export', 'true');
  window.open(`${route('admin.reports.product-views')}?${params.toString()}`, '_blank');
};

onMounted(() => {
  if (trendsChart.value && props.viewsTrends.length > 0) {
    const ctx = trendsChart.value.getContext('2d');
    if (ctx) {
      chart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: props.viewsTrends.map(trend => trend.period),
          datasets: [
            {
              label: 'Total de Visualizações',
              data: props.viewsTrends.map(trend => trend.total_views),
              borderColor: 'rgb(107, 114, 128)',
              backgroundColor: 'rgba(107, 114, 128, 0.1)',
              borderWidth: 2,
              tension: 0.3,
              fill: true
            },
            {
              label: 'Visualizações Únicas',
              data: props.viewsTrends.map(trend => trend.unique_views),
              borderColor: 'rgb(59, 130, 246)',
              backgroundColor: 'rgba(59, 130, 246, 0.1)',
              borderWidth: 2,
              tension: 0.3,
              fill: true
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              title: {
                display: true,
                text: 'Número de Visualizações'
              }
            },
            x: {
              title: {
                display: true,
                text: 'Período'
              }
            }
          }
        }
      });
    }
  }
});
</script>
