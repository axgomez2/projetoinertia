<template>
  <AdminLayout title="Gerenciamento de Clientes">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
          <h1 class="text-2xl font-semibold text-gray-900">Clientes</h1>
          <p class="mt-1 text-sm text-gray-600">
            Gerencie os clientes da loja (usuários com role 20)
          </p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                  <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Total de Clientes</dt>
                    <dd class="flex items-baseline">
                      <div class="text-2xl font-semibold text-gray-900">{{ statistics.total_customers }}</div>
                      <div class="ml-2 flex items-baseline text-sm font-semibold text-green-600">
                        <span>{{ statistics.new_customers_this_month }} novos este mês</span>
                      </div>
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 px-4 py-2 sm:px-6">
              <div class="text-sm">
                <span class="font-medium text-indigo-600 hover:text-indigo-500">
                  Taxa de crescimento: {{ statistics.customer_growth_rate.toFixed(1) }}%
                </span>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                  <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Clientes Verificados</dt>
                    <dd class="flex items-baseline">
                      <div class="text-2xl font-semibold text-gray-900">{{ statistics.verified_customers }}</div>
                      <div class="ml-2 flex items-baseline text-sm font-semibold text-green-600">
                        <span>{{ Math.round(statistics.verification_rate) }}%</span>
                      </div>
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 px-4 py-2 sm:px-6">
              <div class="text-sm">
                <span class="font-medium text-indigo-600 hover:text-indigo-500">
                  Clientes ativos: {{ statistics.active_customers }} ({{ Math.round(statistics.active_customers_rate) }}%)
                </span>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                  <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Clientes com Pedidos</dt>
                    <dd class="flex items-baseline">
                      <div class="text-2xl font-semibold text-gray-900">{{ statistics.customers_with_orders }}</div>
                      <div class="ml-2 flex items-baseline text-sm font-semibold text-green-600">
                        <span>{{ Math.round(statistics.customers_with_orders / statistics.total_customers * 100) || 0 }}%</span>
                      </div>
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 px-4 py-2 sm:px-6">
              <div class="text-sm">
                <span class="font-medium text-indigo-600 hover:text-indigo-500">
                  Média de pedidos por cliente: {{ (statistics.customers_with_orders > 0 ? statistics.total_orders / statistics.customers_with_orders : 0).toFixed(1) }}
                </span>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                  <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Receita Total</dt>
                    <dd class="flex items-baseline">
                      <div class="text-2xl font-semibold text-gray-900">R$ {{ formatCurrency(statistics.total_revenue) }}</div>
                      <div class="ml-2 flex items-baseline text-sm font-semibold text-green-600">
                        <span>Média: R$ {{ formatCurrency(statistics.average_order_value) }}</span>
                      </div>
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 px-4 py-2 sm:px-6">
              <div class="text-sm">
                <span class="font-medium text-indigo-600 hover:text-indigo-500">
                  Valor médio por cliente: R$ {{ formatCurrency(statistics.total_customers > 0 ? statistics.total_revenue / statistics.total_customers : 0) }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow rounded-lg mb-6">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Filtros</h3>
            <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div>
                <label for="search" class="block text-sm font-medium text-gray-700">Busca</label>
                <input type="text" id="search" v-model="filters.search" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Nome ou email">
              </div>

              <div>
                <label for="registration_date_from" class="block text-sm font-medium text-gray-700">Data de Registro (De)</label>
                <input type="date" id="registration_date_from" v-model="filters.registration_date_from" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
              </div>

              <div>
                <label for="registration_date_to" class="block text-sm font-medium text-gray-700">Data de Registro (Até)</label>
                <input type="date" id="registration_date_to" v-model="filters.registration_date_to" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
              </div>

              <div>
                <label for="min_orders" class="block text-sm font-medium text-gray-700">Pedidos (Mínimo)</label>
                <input type="number" id="min_orders" v-model="filters.min_orders" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" min="0">
              </div>

              <div>
                <label for="max_orders" class="block text-sm font-medium text-gray-700">Pedidos (Máximo)</label>
                <input type="number" id="max_orders" v-model="filters.max_orders" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" min="0">
              </div>

              <div>
                <label for="min_spent" class="block text-sm font-medium text-gray-700">Gasto Total (Mínimo)</label>
                <input type="number" id="min_spent" v-model="filters.min_spent" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" min="0" step="0.01">
              </div>

              <div>
                <label for="max_spent" class="block text-sm font-medium text-gray-700">Gasto Total (Máximo)</label>
                <input type="number" id="max_spent" v-model="filters.max_spent" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" min="0" step="0.01">
              </div>

              <div>
                <label for="sort_by" class="block text-sm font-medium text-gray-700">Ordenar por</label>
                <select id="sort_by" v-model="filters.sort_by" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                  <option value="created_at">Data de Registro</option>
                  <option value="name">Nome</option>
                  <option value="email">Email</option>
                  <option value="orders_count">Total de Pedidos</option>
                  <option value="total_spent">Total Gasto</option>
                  <option value="last_order_at">Último Pedido</option>
                </select>
              </div>

              <div>
                <label for="sort_order" class="block text-sm font-medium text-gray-700">Ordem</label>
                <select id="sort_order" v-model="filters.sort_order" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                  <option value="desc">Decrescente</option>
                  <option value="asc">Crescente</option>
                </select>
              </div>

              <div class="lg:col-span-3 flex justify-end space-x-3">
                <button type="button" @click="resetFilters" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                  Limpar Filtros
                </button>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                  Aplicar Filtros
                </button>
                <Link :href="route('admin.customers.export', filters)" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                  Exportar CSV
                </Link>
              </div>
            </form>
          </div>
        </div>

        <!-- Customers Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registro</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pedidos</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Gasto</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Último Pedido</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="customer in customers" :key="customer.id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                      <span class="text-gray-500 font-medium">{{ customer.name.charAt(0).toUpperCase() }}</span>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ customer.name }}</div>
                      <div class="text-sm text-gray-500">{{ customer.email }}</div>
                      <div class="text-xs text-gray-400 mt-1">
                        <span v-if="customer.wishlist_count > 0" class="mr-2">
                          <span class="inline-flex items-center">
                            <svg class="h-3 w-3 text-red-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                              <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
                            </svg>
                            {{ customer.wishlist_count }}
                          </span>
                        </span>
                        <span v-if="customer.wantlist_count > 0" class="mr-2">
                          <span class="inline-flex items-center">
                            <svg class="h-3 w-3 text-blue-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                              <path fill-rule="evenodd" d="M10 3.5a6.5 6.5 0 100 13 6.5 6.5 0 000-13zM2.5 10a7.5 7.5 0 1115 0 7.5 7.5 0 01-15 0z" clip-rule="evenodd" />
                              <path d="M10.75 10a.75.75 0 01-.75.75h-3a.75.75 0 010-1.5h2.25V6.75a.75.75 0 011.5 0V10z" />
                            </svg>
                            {{ customer.wantlist_count }}
                          </span>
                        </span>
                        <span v-if="customer.cart_items_count > 0" class="mr-2">
                          <span class="inline-flex items-center">
                            <svg class="h-3 w-3 text-green-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                              <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
                            </svg>
                            {{ customer.cart_items_count }}
                          </span>
                        </span>
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ formatDate(customer.created_at) }}</div>
                  <div class="text-xs text-gray-500">
                    Última atividade: {{ formatDate(customer.last_activity) }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="customer.status === 'verified' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">
                    {{ customer.status === 'verified' ? 'Verificado' : 'Não Verificado' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <div class="font-medium">{{ customer.orders_count }}</div>
                  <div class="text-xs text-gray-400" v-if="customer.addresses_count > 0">
                    {{ customer.addresses_count }} endereço(s)
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <div class="font-medium">R$ {{ formatCurrency(customer.total_spent) }}</div>
                  <div class="text-xs text-gray-400" v-if="customer.orders_count > 0">
                    Média: R$ {{ formatCurrency(customer.total_spent / customer.orders_count) }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <div>{{ customer.last_order_at ? formatDate(customer.last_order_at) : 'N/A' }}</div>
                  <div class="text-xs text-gray-400" v-if="customer.last_order_at">
                    {{ daysSince(customer.last_order_at) }} dias atrás
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <Link :href="route('admin.customers.show', customer.id)" class="text-indigo-600 hover:text-indigo-900">
                    Ver Detalhes
                  </Link>
                </td>
              </tr>
              <tr v-if="customers.length === 0">
                <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                  Nenhum cliente encontrado
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 mt-4">
          <div class="flex-1 flex justify-between sm:hidden">
            <button @click="previousPage" :disabled="pagination.current_page === 1" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50" :class="{ 'opacity-50 cursor-not-allowed': pagination.current_page === 1 }">
              Anterior
            </button>
            <button @click="nextPage" :disabled="pagination.current_page === pagination.last_page" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50" :class="{ 'opacity-50 cursor-not-allowed': pagination.current_page === pagination.last_page }">
              Próximo
            </button>
          </div>
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700">
                Mostrando <span class="font-medium">{{ ((pagination.current_page - 1) * pagination.per_page) + 1 }}</span> a <span class="font-medium">{{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}</span> de <span class="font-medium">{{ pagination.total }}</span> resultados
              </p>
            </div>
            <div>
              <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <button @click="previousPage" :disabled="pagination.current_page === 1" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50" :class="{ 'opacity-50 cursor-not-allowed': pagination.current_page === 1 }">
                  <span class="sr-only">Anterior</span>
                  <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                </button>
                <button v-for="page in paginationPages" :key="page" @click="goToPage(page)" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium" :class="page === pagination.current_page ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' : 'text-gray-500 hover:bg-gray-50'">
                  {{ page }}
                </button>
                <button @click="nextPage" :disabled="pagination.current_page === pagination.last_page" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50" :class="{ 'opacity-50 cursor-not-allowed': pagination.current_page === pagination.last_page }">
                  <span class="sr-only">Próximo</span>
                  <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                  </svg>
                </button>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';

const props = defineProps({
  customers: Array,
  filters: Object,
  pagination: Object,
  statistics: Object,
});

const filters = ref({
  search: props.filters?.search || '',
  registration_date_from: props.filters?.registration_date_from || '',
  registration_date_to: props.filters?.registration_date_to || '',
  min_orders: props.filters?.min_orders || '',
  max_orders: props.filters?.max_orders || '',
  min_spent: props.filters?.min_spent || '',
  max_spent: props.filters?.max_spent || '',
  sort_by: props.filters?.sort_by || 'created_at',
  sort_order: props.filters?.sort_order || 'desc',
  page: props.pagination?.current_page || 1,
});

const paginationPages = computed(() => {
  const currentPage = props.pagination.current_page;
  const lastPage = props.pagination.last_page;

  let pages = [];

  if (lastPage <= 7) {
    for (let i = 1; i <= lastPage; i++) {
      pages.push(i);
    }
  } else {
    if (currentPage <= 3) {
      pages = [1, 2, 3, 4, 5, '...', lastPage];
    } else if (currentPage >= lastPage - 2) {
      pages = [1, '...', lastPage - 4, lastPage - 3, lastPage - 2, lastPage - 1, lastPage];
    } else {
      pages = [1, '...', currentPage - 1, currentPage, currentPage + 1, '...', lastPage];
    }
  }

  return pages;
});

const applyFilters = () => {
  router.get(route('admin.customers.index'), {
    ...filters.value,
    page: 1, // Reset to first page when applying filters
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const resetFilters = () => {
  filters.value = {
    search: '',
    registration_date_from: '',
    registration_date_to: '',
    min_orders: '',
    max_orders: '',
    min_spent: '',
    max_spent: '',
    sort_by: 'created_at',
    sort_order: 'desc',
    page: 1,
  };

  router.get(route('admin.customers.index'), filters.value, {
    preserveState: true,
    preserveScroll: true,
  });
};

const goToPage = (page) => {
  if (typeof page === 'number') {
    router.get(route('admin.customers.index'), {
      ...filters.value,
      page: page,
    }, {
      preserveState: true,
      preserveScroll: true,
    });
  }
};

const previousPage = () => {
  if (props.pagination.current_page > 1) {
    goToPage(props.pagination.current_page - 1);
  }
};

const nextPage = () => {
  if (props.pagination.current_page < props.pagination.last_page) {
    goToPage(props.pagination.current_page + 1);
  }
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return new Intl.DateTimeFormat('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  }).format(date);
};

const formatCurrency = (value) => {
  if (value === null || value === undefined) return '0,00';
  return parseFloat(value).toLocaleString('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};

const daysSince = (dateString) => {
  if (!dateString) return 0;
  const date = new Date(dateString);
  const today = new Date();
  const diffTime = Math.abs(today - date);
  return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
};
</script>
