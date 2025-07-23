<template>
  <AdminLayout>
    <div class="p-6">
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Relatório de Lista de Desejos</h1>
        <p class="text-gray-600">Análise de preferências dos clientes através das listas de desejos</p>
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

      <!-- Wishlist Stats -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-2 bg-red-100 rounded-lg">
              <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Total de Itens na Lista de Desejos</p>
              <p class="text-2xl font-bold text-gray-900">{{ wishlistStats.total_wishlist_items }}</p>
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
              <p class="text-sm font-medium text-gray-600">Clientes Únicos</p>
              <p class="text-2xl font-bold text-gray-900">{{ wishlistStats.unique_customers }}</p>
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
              <p class="text-sm font-medium text-gray-600">Média de Itens por Cliente</p>
              <p class="text-2xl font-bold text-gray-900">{{ wishlistStats.average_items_per_customer }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Wishlist Trends -->
      <div class="bg-white rounded-lg shadow mb-8">
        <div class="p-6 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Tendências da Lista de Desejos</h3>
        </div>
        <div class="p-6">
          <div v-if="wishlistTrends.length === 0" class="text-center text-gray-500 py-8">
            Nenhum dado encontrado para o período selecionado
          </div>
          <div v-else class="h-80">
            <canvas ref="trendsChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Most Wishlisted Products -->
      <div class="bg-white rounded-lg shadow mb-8">
        <div class="p-6 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Produtos Mais Desejados</h3>
        </div>
        <div class="p-6">
          <div v-if="mostWishlistedProducts.length === 0" class="text-center text-gray-500 py-8">
            Nenhum dado encontrado para o período selecionado
          </div>
          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produto</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contagem</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preço</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estoque</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="product in mostWishlistedProducts" :key="product.title">
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
                    <div class="text-sm text-gray-900">{{ product.wishlist_count }}</div>
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

      <!-- Customer Behavior -->
      <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Comportamento dos Clientes</h3>
        </div>
        <div class="p-6">
          <div v-if="customerBehavior.length === 0" class="text-center text-gray-500 py-8">
            Nenhum dado encontrado para o período selecionado
          </div>
          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Itens na Lista</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Última Atividade</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="customer in customerBehavior" :key="customer.email">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ customer.name }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ customer.email }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ customer.items_wishlisted }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ formatDate(customer.last_wishlist_activity) }}</div>
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

interface WishlistStats {
  total_wishlist_items: number;
  unique_customers: number;
  average_items_per_customer: number;
}

interface WishlistedProduct {
  title: string;
  cover_image: string | null;
  wishlist_count: number;
  price: number;
  stock: number;
}

interface WishlistTrend {
  period: string;
  wishlist_additions: number;
}

interface CustomerBehavior {
  name: string;
  email: string;
  items_wishlisted: number;
  last_wishlist_activity: string;
}

interface Props {
  wishlistStats: WishlistStats;
  mostWishlistedProducts: WishlistedProduct[];
  wishlistTrends: WishlistTrend[];
  customerBehavior: CustomerBehavior[];
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

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
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
  router.get(route('admin.reports.wishlist'), filters, {
    preserveState: true,
    preserveScroll: true
  });
};

const exportData = () => {
  const params = new URLSearchParams(filters as any);
  params.append('export', 'true');
  window.open(`${route('admin.reports.wishlist')}?${params.toString()}`, '_blank');
};

onMounted(() => {
  if (trendsChart.value && props.wishlistTrends.length > 0) {
    const ctx = trendsChart.value.getContext('2d');
    if (ctx) {
      chart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: props.wishlistTrends.map(trend => trend.period),
          datasets: [
            {
              label: 'Adições à Lista de Desejos',
              data: props.wishlistTrends.map(trend => trend.wishlist_additions),
              borderColor: 'rgb(220, 38, 38)',
              backgroundColor: 'rgba(220, 38, 38, 0.1)',
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
                text: 'Número de Adições'
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
