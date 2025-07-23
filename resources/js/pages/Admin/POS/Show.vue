<template>
  <AdminLayout>
    <div class="p-6">
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Detalhes da Venda</h1>
          <p class="text-gray-600">{{ sale.invoice_number }}</p>
        </div>
        <div class="flex space-x-3">
          <button
            @click="printReceipt"
            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700"
          >
            Imprimir Recibo
          </button>
          <Link
            :href="route('admin.pos.index')"
            class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700"
          >
            Voltar
          </Link>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sale Information -->
        <div class="lg:col-span-2">
          <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4 flex items-center">
              <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              Informações da Venda
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Número da Venda</label>
                <p class="mt-1 text-sm text-gray-900">{{ sale.invoice_number }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Data da Venda</label>
                <p class="mt-1 text-sm text-gray-900">{{ formatDate(sale.created_at) }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Cliente</label>
                <p class="mt-1 text-sm text-gray-900">{{ sale.customer_display_name }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Vendedor</label>
                <p class="mt-1 text-sm text-gray-900">{{ sale.user?.name || 'N/A' }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Método de Pagamento</label>
                <span class="mt-1 inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                  {{ sale.payment_method_label }}
                </span>
              </div>

              <div v-if="sale.notes">
                <label class="block text-sm font-medium text-gray-700">Observações</label>
                <p class="mt-1 text-sm text-gray-900">{{ sale.notes }}</p>
              </div>
            </div>
          </div>

          <!-- Sale Items -->
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4 flex items-center">
              <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
              </svg>
              Itens da Venda
            </h2>

            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Produto
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Preço Unit.
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Qtd.
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Desconto
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Total
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="item in sale.items" :key="item.id">
                    <td class="px-6 py-4">
                      <div>
                        <div class="text-sm font-medium text-gray-900">
                          {{ item.product_name }}
                        </div>
                        <div class="text-sm text-gray-500">
                          {{ item.product_artist }}
                        </div>
                        <div class="text-xs text-gray-400">
                          Código: {{ item.vinyl_sec?.catalog_number || item.vinyl_sec?.internal_code }}
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      R$ {{ item.price }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ item.quantity }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      R$ {{ item.item_discount || '0.00' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      R$ {{ item.item_total }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Sale Summary -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4 flex items-center">
              <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
              </svg>
              Resumo da Venda
            </h2>

            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Subtotal:</span>
                <span class="text-sm font-medium">R$ {{ sale.subtotal }}</span>
              </div>

              <div v-if="sale.discount > 0" class="flex justify-between">
                <span class="text-sm text-gray-600">Desconto:</span>
                <span class="text-sm font-medium text-red-600">- R$ {{ sale.discount }}</span>
              </div>

              <div v-if="sale.shipping > 0" class="flex justify-between">
                <span class="text-sm text-gray-600">Frete:</span>
                <span class="text-sm font-medium">R$ {{ sale.shipping }}</span>
              </div>

              <div class="border-t pt-3">
                <div class="flex justify-between">
                  <span class="text-lg font-semibold">Total:</span>
                  <span class="text-lg font-bold text-green-600">R$ {{ sale.total }}</span>
                </div>
              </div>
            </div>

            <div class="mt-6 pt-6 border-t">
              <h3 class="text-sm font-medium text-gray-700 mb-2">Estatísticas</h3>
              <div class="space-y-2">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Total de itens:</span>
                  <span>{{ sale.items.reduce((sum, item) => sum + item.quantity, 0) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Produtos únicos:</span>
                  <span>{{ sale.items.length }}</span>
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
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/admin/AdminLayout.vue'

interface SaleItem {
  id: number
  product_name: string
  product_artist: string
  price: string
  quantity: number
  item_discount: string
  item_total: string
  vinyl_sec?: {
    catalog_number?: string
    internal_code?: string
  }
}

interface Sale {
  id: number
  invoice_number: string
  customer_display_name: string
  payment_method_label: string
  subtotal: string
  discount: string
  shipping: string
  total: string
  notes?: string
  created_at: string
  user?: {
    name: string
  }
  items: SaleItem[]
}

interface Props {
  sale: Sale
}

const props = defineProps<Props>()

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleString('pt-BR')
}

const printReceipt = async () => {
  try {
    const response = await fetch(`/admin/pos/${props.sale.id}/receipt`)
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
          body { font-family: Arial, sans-serif; max-width: 300px; margin: 0 auto; padding: 20px; }
          .header { text-align: center; border-bottom: 1px solid #ccc; padding-bottom: 10px; margin-bottom: 10px; }
          .item { display: flex; justify-content: space-between; margin: 5px 0; }
          .total { border-top: 1px solid #ccc; padding-top: 10px; font-weight: bold; }
          .footer { text-align: center; margin-top: 20px; font-size: 12px; }
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
            <span>Subtotal:</span>
            <span>R$ ${sale.subtotal}</span>
          </div>
          ${sale.discount > 0 ? `
          <div class="item">
            <span>Desconto:</span>
            <span>- R$ ${sale.discount}</span>
          </div>
          ` : ''}
          <div class="item">
            <span>Total:</span>
            <span>R$ ${sale.total}</span>
          </div>
        </div>

        <div class="footer">
          <p>Obrigado pela preferência!</p>
          <p>${storeData.generated_at}</p>
        </div>
      </body>
    </html>
  `
}
</script>
