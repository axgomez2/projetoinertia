<template>
  <AdminLayout>
    <div class="p-6">
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Relatório de Inventário por Fornecedor</h1>
        <p class="text-gray-600">Análise de estoque, custos e preços por fornecedor</p>
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
            <label class="block text-sm font-medium text-gray-700 mb-1">Fornecedor</label>
            <select
              v-model="filters.supplier_id"
              class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Todos os Fornecedores</option>
              <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                {{ supplier.name }}
              </option>
            </select>
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
      <!-- Supplier Summary -->
      <div class="bg-white rounded-lg shadow mb-8">
        <div class="p-6 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Resumo por Fornecedor</h3>
        </div>
        <div class="p-6">
          <div v-if="supplierSummary.length === 0" class="text-center text-gray-500 py-8">
            Nenhum dado encontrado para o período selecionado
          </div>
          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fornecedor</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total de Itens</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor de Compra</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor de Venda</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Margem</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="supplier in supplierSummary" :key="supplier.id">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ supplier.name }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ supplier.total_items }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">R$ {{ formatCurrency(supplier.total_cost) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">R$ {{ formatCurrency(supplier.total_price) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm" :class="getMarginClass(supplier.margin_percentage)">
                      {{ supplier.margin_percentage.toFixed(2) }}%
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <!-- Inventory Details -->
      <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Detalhes do Inventário</h3>
        </div>
        <div class="p-6">
          <div v-if="inventoryItems.data.length === 0" class="text-center text-gray-500 py-8">
            Nenhum item encontrado para o período selecionado
          </div>
          <div v-else>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produto</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fornecedor</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Custo</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preço</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estoque</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Margem</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="item in inventoryItems.data" :key="item.id">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                          <img class="h-10 w-10 rounded-full object-cover" :src="item.cover_image || '/images/vinyl-placeholder.png'" :alt="item.title">
                        </div>
                        <div class="ml-4">
                          <div class="text-sm font-medium text-gray-900">{{ item.title }}</div>
                          <div class="text-xs text-gray-500">{{ item.artist }}</div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">{{ item.supplier_name || 'Sem Fornecedor' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">R$ {{ formatCurrency(item.cost) }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">R$ {{ formatCurrency(item.price) }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="getStockBadgeClass(item.stock)">
                        {{ item.stock }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm" :class="getMarginClass(item.margin_percentage)">
                        {{ item.margin_percentage.toFixed(2) }}%
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <!-- Pagination -->
            <div class="mt-6">
              <Pagination :links="inventoryItems.links" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import Pagination from '@/components/Pagination.vue';

const props = defineProps({
  inventoryItems: Object,
  supplierSummary: Array,
  suppliers: Array,
  dateRange: Array,
  filters: Object
});

const filters = reactive({
  start_date: props.filters.start_date || '',
  end_date: props.filters.end_date || '',
  supplier_id: props.filters.supplier_id || ''
});

const formatCurrency = (value) => {
  return new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value);
};

const getStockBadgeClass = (stock) => {
  if (stock === 0) {
    return 'px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800';
  } else if (stock <= 5) {
    return 'px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800';
  } else {
    return 'px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800';
  }
};

const getMarginClass = (margin) => {
  if (margin < 15) {
    return 'text-red-600';
  } else if (margin < 30) {
    return 'text-yellow-600';
  } else {
    return 'text-green-600';
  }
};

const applyFilters = () => {
  router.get(route('admin.reports.supplier-inventory'), filters, {
    preserveState: true,
    preserveScroll: true
  });
};

const exportData = () => {
  const params = new URLSearchParams({...filters, export: true});
  window.open(`${route('admin.reports.supplier-inventory')}?${params.toString()}`, '_blank');
};
</script>
