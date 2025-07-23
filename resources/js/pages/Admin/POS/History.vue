<template>
  <AdminLayout>
    <div class="p-6">
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Histórico de Vendas - PDV</h1>
          <p class="text-gray-600">Visualize e gerencie o histórico de vendas do ponto de venda</p>
        </div>
        <Link
          :href="route('admin.pos.create')"
          class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"
        >
          Nova Venda
        </Link>
      </div>

      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4 hover:shadow-md transition-shadow">
          <div class="flex items-center">
            <div class="p-2 bg-blue-100 rounded-lg mr-3">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
              </svg>
            </div>
            <div>
              <div class="text-2xl font-bold text-blue-600">{{ stats.total_sales }}</div>
              <div class="text-sm text-gray-600">Total de Vendas</div>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 hover:shadow-md transition-shadow">
          <div class="flex items-center">
            <div class="p-2 bg-green-100 rounded-lg mr-3">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
              </svg>
            </div>
            <div>
              <div class="text-2xl font-bold text-green-600">R$ {{ stats.total_revenue.toFixed(2) }}</div>
              <div class="text-sm text-gray-600">Receita Total</div>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 hover:shadow-md transition-shadow">
          <div class="flex items-center">
            <div class="p-2 bg-purple-100 rounded-lg mr-3">
              <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
              </svg>
            </div>
            <div>
              <div class="text-2xl font-bold text-purple-600">R$ {{ stats.average_sale.toFixed(2) }}</div>
              <div class="text-sm text-gray-600">Venda Média</div>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 hover:shadow-md transition-shadow">
          <div class="flex items-center">
            <div class="p-2 bg-orange-100 rounded-lg mr-3">
              <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
              </svg>
            </div>
            <div>
              <div class="text-2xl font-bold text-orange-600">{{ stats.total_items_sold }}</div>
              <div class="text-sm text-gray-600">Itens Vendidos</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
            <input
              v-model="filters.search"
              @input="applyFilters"
              type="text"
              placeholder="Número da venda, cliente..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Método de Pagamento</label>
            <select
              v-model="filters.payment_method"
              @change="applyFilters"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Todos</option>
              <option
                v-for="method in paymentMethods"
                :key="method.value"
                :value="method.value"
              >
                {{ method.label }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Período</label>
            <select
              v-model="filters.date_range"
              @change="applyFilters"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Todos</option>
              <option value="today">Hoje</option>
              <option value="yesterday">Ontem</option>
              <option value="this_week">Esta Semana</option>
              <option value="last_week">Semana Passada</option>
              <option value="this_month">Este Mês</option>
              <option value="last_month">Mês Passado</option>
            </select>
          </div>

          <div class="flex items-end">
            <button
              @click="clearFilters"
              class="w-full px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
            >
              Limpar Filtros
            </button>
          </div>
        </div>
      </div>

      <!-- Sales Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <!-- Mobile Cards View -->
        <div class="block sm:hidden">
          <div class="divide-y divide-gray-200">
            <div v-for="sale in sales.data" :key="sale.id" class="p-4 hover:bg-gray-50">
              <div class="flex justify-between items-start mb-2">
                <div>
                  <div class="font-medium text-gray-900">{{ sale.invoice_number }}</div>
                  <div class="text-sm text-gray-600">{{ sale.customer_display_name }}</div>
                </div>
                <div class="text-right">
                  <div class="font-medium text-gray-900">R$ {{ sale.total }}</div>
                  <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                    {{ sale.payment_method_label }}
                  </span>
                </div>
              </div>
              <div class="flex justify-between items-center text-sm text-gray-500">
                <span>{{ sale.items.length }} item(s)</span>
                <span>{{ formatDate(sale.created_at) }}</span>
              </div>
              <div class="flex justify-end space-x-2 mt-3">
                <Link
                  :href="route('admin.pos.show', sale.id)"
                  class="text-blue-600 hover:text-blue-900 text-sm"
                >
                  Ver
                </Link>
                <button
                  @click="printReceipt(sale.id)"
                  class="text-green-600 hover:text-green-900 text-sm"
                >
                  Recibo
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Desktop Table View -->
        <div class="hidden sm:block overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Venda
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Cliente
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Itens
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Pagamento
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Total
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Data
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Ações
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="sale in sales.data" :key="sale.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ sale.invoice_number }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ sale.customer_display_name }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ sale.items.length }} item(s)</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                    {{ sale.payment_method_label }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">R$ {{ sale.total }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ formatDate(sale.created_at) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <Link
                      :href="route('admin.pos.show', sale.id)"
                      class="text-blue-600 hover:text-blue-900"
                    >
                      Ver
                    </Link>
                    <button
                      @click="printReceipt(sale.id)"
                      class="text-green-600 hover:text-green-900"
                    >
                      Recibo
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-if="sales.data.length === 0" class="text-center py-12">
          <div class="text-gray-500">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma venda encontrada</h3>
            <p class="mt-1 text-sm text-gray-500">Comece criando uma nova venda no PDV.</p>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="sales.data.length > 0" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
          <Pagination :links="sales.links" />
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/admin/AdminLayout.vue'
import Pagination from '@/components/Pagination.vue'

interface Sale {
  id: number
  invoice_number: string
  customer_display_name: string
  payment_method_label: string
  total: string
  created_at: string
  items: Array<any>
}

interface Props {
  sales: {
    data: Sale[]
    links: Array<any>
  }
  stats: {
    total_sales: number
    total_revenue: number
    average_sale: number
    total_items_sold: number
  }
  filters: {
    search?: string
    payment_method?: string
    date_range?: string
  }
  paymentMethods: Array<{ value: string; label: string }>
}

const props = defineProps<Props>()

const filters = reactive({
  search: props.filters.search || '',
  payment_method: props.filters.payment_method || '',
  date_range: props.filters.date_range || '',
})

const applyFilters = () => {
  router.get(route('admin.pos.index'), filters, {
    preserveState: true,
    replace: true,
  })
}

const clearFilters = () => {
  filters.search = ''
  filters.payment_method = ''
  filters.date_range = ''
  applyFilters()
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleString('pt-BR')
}

const printReceipt = async (saleId: number) => {
  try {
    const response = await fetch(`/admin/pos/${saleId}/receipt`)
    const receiptData = await response.json()

    // Open receipt in new window for printing
    const printWindow = window.open('', '_blank')
    if (printWindow) {
      printWindow.document.write(generateReceiptHTML(receiptData))
      printWindow.document.close()
      printWindow.print()
    }
  } catch (error) {
    console.error('Erro ao gerar recibo:', error)
    alert('Erro ao gerar recibo')
  }
}

const generateReceiptHTML = (receiptData: any) => {
  const sale = receiptData.sale
  const storeData = receiptData.receipt_data

  return `
    <html>
      <head>
        <title>Recibo - ${sale.invoice_number}</title>
        <style>
          body { font-family: Arial, sans-serif; max-width: 300px; margin: 0 auto; }
          .header { text-align: center; border-bottom: 1px solid #ccc; padding-bottom: 10px; margin-bottom: 10px; }
          .item { display: flex; justify-content: space-between; margin: 5px 0; }
          .total { border-top: 1px solid #ccc; padding-top: 10px; font-weight: bold; }
        </style>
      </head>
      <body>
        <div class="header">
          <h2>${storeData.store_name}</h2>
          <p>${storeData.store_address}</p>
          <p>${storeData.store_phone}</p>
        </div>

        <p><strong>Recibo:</strong> ${sale.invoice_number}</p>
        <p><strong>Data:</strong> ${new Date(sale.created_at).toLocaleString('pt-BR')}</p>
        <p><strong>Cliente:</strong> ${sale.customer_display_name}</p>
        <p><strong>Pagamento:</strong> ${sale.payment_method_label}</p>

        <hr>

        ${sale.items.map(item => `
          <div class="item">
            <span>${item.product_name} (${item.quantity}x)</span>
            <span>R$ ${(item.price * item.quantity).toFixed(2)}</span>
          </div>
        `).join('')}

        <div class="total">
          <div class="item">
            <span>Total:</span>
            <span>R$ ${sale.total}</span>
          </div>
        </div>

        <p style="text-align: center; margin-top: 20px; font-size: 12px;">
          Obrigado pela preferência!<br>
          ${storeData.generated_at}
        </p>
      </body>
    </html>
  `
}
</script>
