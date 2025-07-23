<template>
  <AdminLayout>
    <div class="p-6">
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Relatório de Lista de Procurados</h1>
        <p class="text-gray-600">Análise de demanda dos clientes através das listas de procurados</p>
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

      <!-- Wantlist Stats -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-2 bg-indigo-100 rounded-lg">
              <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Total de Itens Procurados</p>
              <p class="text-2xl font-bold text-gray-900">{{ wantlistStats.total_wantlist_items }}</p>
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
              <p class="text-2xl font-bold text-gray-900">{{ wantlistStats.unique_customers }}</p>
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
              <p class="text-2xl font-bold text-gray-900">{{ wantlistStats.average_items_per_customer }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Demand Trends -->
      <div class="bg-white rounded-lg shadow mb-8">
        <div class="p-6 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Tendências de Demanda</h3>
        </div>
        <div class="p-6">
          <div v-if="demandTrends.length === 0" class="text-center text-gray-500 py-8">
            Nenhum dado encontrado para o período selecionado
          </div>
          <div v-else class="h-80">
            <canvas ref="trendsChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Most Wanted Products -->
      <div class="bg-white rounded-lg shadow mb-8">
        <div class="p-6 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Produtos Mais Procurados</h3>
        </div>
        <div class="p-6">
          <div v-if="mostWantedProducts.length === 0" class="text-center text-gray-500 py-8">
            Nenhum dado encontrado para o período selecionado
          </div>
          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produto</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Demanda</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preço</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Disponibilidade</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="product in mostWantedProducts" :key="product.title">
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
                    <div class="text-sm text-gray-900">{{ product.demand_count }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">R$ {{ formatCurrency(product.price) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="getAvailabilityBadgeClass(product.availability_status)">
                      {{ getAvailabilityLabel(product.availability_status) }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Customer Demand Behavior -->
      <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Comportamento de Demanda dos Clientes</h3>
        </div>
        <div class="p-6">
          <div v-if="demandBehavior.length === 0" class="text-center text-gray-500 py-8">
            Nenhum dado encontrado para o período selecionado
          </div>
          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Itens Procurados</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Última Atividade</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="customer in demandBehavior" :key="customer.email">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ customer.name }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ customer.email }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ customer.items_wanted }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ formatDate(customer.last_wantlist_activity) }}</div>
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

interface WantlistStats {
  total_wantlist_items: number;
  unique_customers: number;
  average_items_per_customer: number;
}

interface WantedProduct {
  title: string;
  cover_image: string | null;
  demand_count: number;
  price: number;
  stock: number;
  availability_status: 'out_of_stock' | 'low_stock' | 'in_stock';
}

interface DemandTrend {
  period: string;
  wantlist_additions: number;
}

interface CustomerBehavior {
  name: string;
  email: string;
  items_wanted: number;
  last_wantlist_activity: string;
}

interface Props {
  wantlistStats: WantlistStats;
  mostWantedProducts: WantedProduct[];
  demandTrends: DemandTrend[];
  demandBehavior: CustomerBehavior[];
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

const getAvailabilityBadgeClass = (status: string) => {
  if (status === 'out_of_stock') {
    return 'px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800';
  } else if (status === 'low_stock') {
    return 'px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800';
  } else {
    return 'px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800';
  }
};

const getAvailabilityLabel = (status: string) => {
  const labels: Record<string, string> = {
    out_of_stock: 'Sem Estoque',
    low_stock: 'Estoque Baixo',
    in_stock: 'Em Estoque'
  };
  return labels[status] || status;
};

const applyFilters = () => {
  router.get(route('admin.reports.wantlist'), filters, {
    preserveState: true,
    preserveScroll: true
  });
};

const exportData = () => {
  const params = new URLSearchParams(filters as any);
  params.append('export', 'true');
  window.open(`${route('admin.reports.wantlist')}?${params.toString()}`, '_blank');
};

onMounted(() => {
  if (trendsChart.value && props.demandTrends.length > 0) {
    const ctx = trendsChart.value.getContext('2d');
    if (ctx) {
      chart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: props.demandTrends.map(trend => trend.period),
          datasets: [
            {
              label: 'Adições à Lista de Procurados',
              data: props.demandTrends.map(trend => trend.wantlist_additions),
              borderColor: 'rgb(79, 70, 229)',
              backgroundColor: 'rgba(79, 70, 229, 0.1)',
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
