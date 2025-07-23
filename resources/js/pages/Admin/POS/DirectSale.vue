<template>
    <AdminLayout>
        <div class="p-6">
            <div class="mb-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Nova Venda - PDV</h1>
                        <p class="text-gray-600">Registre uma venda direta no ponto de venda</p>
                    </div>
                    <div class="hidden sm:block">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-xs text-blue-700">
                            <div class="font-medium mb-1">Atalhos do Teclado:</div>
                            <div>F1 - Focar busca</div>
                            <div>Ctrl+Enter - Finalizar venda</div>
                            <div>Esc - Limpar carrinho</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Product Search and Selection -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Buscar Produtos
                    </h2>

                    <div class="mb-4 relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input v-model="searchQuery" @input="searchProducts" type="text"
                            placeholder="Buscar por título, artista, código..."
                            class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base" />
                        <div v-if="isSearching" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <svg class="animate-spin h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </div>
                    </div>

                    <div v-if="isSearching && searchQuery" class="flex items-center justify-center py-8 text-gray-500">
                        <svg class="animate-spin h-6 w-6 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Buscando produtos...
                    </div>

                    <div v-else-if="searchResults.length > 0"
                        class="space-y-2 max-h-64 overflow-y-auto border rounded-lg">
                        <div v-for="product in searchResults" :key="product.id" @click="addToCart(product)"
                            class="p-3 border-b last:border-b-0 cursor-pointer hover:bg-blue-50 transition-colors flex justify-between items-center">
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">{{ product.title }}</div>
                                <div class="text-sm text-gray-600">{{ product.artist }}</div>
                                <div class="text-xs text-gray-500 mt-1">
                                    <span class="inline-block mr-3">Código: {{ product.catalog_number ||
                                        product.internal_code }}</span>
                                    <span class="inline-block mr-3">Formato: {{ product.format }}</span>
                                    <span class="inline-block">Mídia: {{ product.midia_status }}</span>
                                </div>
                                <div class="text-xs text-gray-500">
                                    Capa: {{ product.cover_status }} | Estoque: {{ product.stock }} unidades
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-semibold text-green-600">
                                    R$ {{ product.price.toFixed(2) }}
                                </div>
                                <button @click.stop="addToCart(product)"
                                    class="mt-1 px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">
                                    Adicionar
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="searchQuery && !isSearching" class="text-gray-500 text-center py-4">
                        Nenhum produto encontrado
                    </div>
                </div>

                <!-- Cart -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 11-4 0v-6m4 0V9a2 2 0 10-4 0v4.01">
                            </path>
                        </svg>
                        Carrinho de Venda
                    </h2>

                    <div v-if="cartItems.length === 0" class="text-gray-500 text-center py-8">
                        Nenhum item no carrinho
                    </div>

                    <div v-else class="space-y-3">
                        <div v-for="(item, index) in cartItems" :key="index"
                            class="p-3 border rounded-lg bg-gray-50 hover:bg-gray-100 transition-colors">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex-1 mb-3 sm:mb-0">
                                    <div class="font-medium text-gray-900 text-sm sm:text-base">{{ item.title }}</div>
                                    <div class="text-sm text-gray-600">{{ item.artist }}</div>
                                    <div class="text-sm text-gray-500">R$ {{ item.price.toFixed(2) }} cada</div>
                                    <div class="text-xs text-gray-400">
                                        Código: {{ item.catalog_number || item.internal_code }}
                                    </div>
                                </div>

                                <div class="flex items-center justify-between sm:justify-end space-x-2">
                                    <div class="flex items-center space-x-2">
                                        <button @click="updateQuantity(index, item.quantity - 1)"
                                            :disabled="item.quantity <= 1"
                                            class="w-8 h-8 flex items-center justify-center bg-red-100 text-red-600 rounded-full hover:bg-red-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                            -
                                        </button>
                                        <span class="w-12 text-center font-medium">{{ item.quantity }}</span>
                                        <button @click="updateQuantity(index, item.quantity + 1)"
                                            :disabled="item.quantity >= item.stock"
                                            class="w-8 h-8 flex items-center justify-center bg-green-100 text-green-600 rounded-full hover:bg-green-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                            +
                                        </button>
                                    </div>
                                    <button @click="removeFromCart(index)"
                                        class="w-8 h-8 flex items-center justify-center text-red-600 hover:text-red-800 hover:bg-red-100 rounded-full transition-colors"
                                        title="Remover item">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Cart Summary -->
                        <div class="border-t pt-4 mt-4 bg-white p-3 rounded-lg">
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Subtotal:</span>
                                    <span>R$ {{ cartTotal.toFixed(2) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Itens:</span>
                                    <span>{{cartItems.reduce((sum, item) => sum + item.quantity, 0)}}</span>
                                </div>
                                <div class="flex justify-between text-lg font-semibold border-t pt-2">
                                    <span>Total:</span>
                                    <span class="text-green-600">R$ {{ cartTotal.toFixed(2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sale Form -->
            <div v-if="cartItems.length > 0" class="mt-6 bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                    Finalizar Venda
                </h2>

                <form @submit.prevent="processSale" class="space-y-6">
                    <!-- Customer Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nome do Cliente (opcional)
                            </label>
                            <input v-model="form.customer_name" type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Digite o nome do cliente" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Método de Pagamento *
                            </label>
                            <select v-model="form.payment_method" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="{ 'border-red-300': !form.payment_method && showValidation }">
                                <option value="">Selecione o método de pagamento</option>
                                <option v-for="method in paymentMethods" :key="method.value" :value="method.value">
                                    {{ method.label }}
                                </option>
                            </select>
                            <p v-if="!form.payment_method && showValidation" class="mt-1 text-sm text-red-600">
                                Selecione um método de pagamento
                            </p>
                        </div>
                    </div>

                    <!-- Payment Method Details -->
                    <div v-if="form.payment_method" class="p-4 bg-blue-50 rounded-lg">
                        <h3 class="text-sm font-medium text-blue-900 mb-2">
                            Detalhes do Pagamento - {{paymentMethods.find(m => m.value === form.payment_method)?.label
                            }}
                        </h3>
                        <div v-if="form.payment_method === 'money'" class="text-sm text-blue-700">
                            <p>💰 Pagamento em dinheiro - Certifique-se de ter o troco necessário</p>
                        </div>
                        <div v-else-if="form.payment_method === 'credit_card'" class="text-sm text-blue-700">
                            <p>💳 Cartão de Crédito - Processe o pagamento na maquininha</p>
                        </div>
                        <div v-else-if="form.payment_method === 'debit_card'" class="text-sm text-blue-700">
                            <p>💳 Cartão de Débito - Processe o pagamento na maquininha</p>
                        </div>
                        <div v-else-if="form.payment_method === 'pix'" class="text-sm text-blue-700">
                            <p>📱 PIX - Gere o QR Code ou chave PIX para o cliente</p>
                        </div>
                        <div v-else-if="form.payment_method === 'bank_transfer'" class="text-sm text-blue-700">
                            <p>🏦 Transferência Bancária - Forneça os dados bancários ao cliente</p>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <h3 class="text-sm font-medium text-gray-900 mb-3">Resumo do Pedido</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span>Subtotal ({{cartItems.reduce((sum, item) => sum + item.quantity, 0)}}
                                    itens):</span>
                                <span>R$ {{ cartTotal.toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between font-semibold text-lg border-t pt-2">
                                <span>Total a Pagar:</span>
                                <span class="text-green-600">R$ {{ cartTotal.toFixed(2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Observações
                        </label>
                        <textarea v-model="form.notes" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Observações sobre a venda, desconto aplicado, etc..."></textarea>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-3 pt-4 border-t">
                        <button type="button" @click="clearCart"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                            Limpar Carrinho
                        </button>
                        <button type="submit" :disabled="processing || cartItems.length === 0"
                            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center">
                            <svg v-if="processing" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            {{ processing ? 'Processando Venda...' : 'Finalizar Venda' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Error Toast -->
            <div v-if="showError" class="fixed top-4 right-4 z-50 max-w-sm">
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-sm">{{ errorMessage }}</span>
                        <button @click="showError = false" class="ml-2 text-red-700 hover:text-red-900">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Success Modal -->
            <div v-if="showSuccessModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                    <div class="text-center">
                        <div class="text-green-600 text-4xl mb-4">✓</div>
                        <h3 class="text-lg font-semibold mb-2">Venda Realizada com Sucesso!</h3>
                        <p class="text-gray-600 mb-4">
                            Número da venda: {{ lastSale?.invoice_number }}
                        </p>
                        <div class="flex justify-center space-x-3">
                            <button @click="printReceipt"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Imprimir Recibo
                            </button>
                            <button @click="newSale"
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                Nova Venda
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import AdminLayout from '@/layouts/admin/AdminLayout.vue'

interface Product {
    id: number
    title: string
    artist: string
    catalog_number: string
    internal_code: string
    price: number
    stock: number
    format: string
    midia_status: string
    cover_status: string
}

interface CartItem extends Product {
    quantity: number
}

interface Props {
    paymentMethods: Array<{ value: string; label: string }>
}

defineProps<Props>()

const searchQuery = ref('')
const searchResults = ref<Product[]>([])
const isSearching = ref(false)
const cartItems = ref<CartItem[]>([])
const processing = ref(false)
const showSuccessModal = ref(false)
const lastSale = ref<any>(null)
const showValidation = ref(false)
const errorMessage = ref('')
const showError = ref(false)

const form = ref({
    customer_name: '',
    payment_method: '',
    notes: '',
})

const cartTotal = computed(() => {
    return cartItems.value.reduce((total, item) => total + (item.price * item.quantity), 0)
})

let searchTimeout: ReturnType<typeof setTimeout>

const searchProducts = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout)
    }

    if (!searchQuery.value.trim()) {
        searchResults.value = []
        return
    }

    isSearching.value = true

    searchTimeout = setTimeout(async () => {
        try {
            const response = await fetch(`/admin/pos/search-products?search=${encodeURIComponent(searchQuery.value)}`)
            const products = await response.json()
            searchResults.value = products
        } catch (error) {
            console.error('Erro ao buscar produtos:', error)
        } finally {
            isSearching.value = false
        }
    }, 300)
}

const addToCart = (product: Product) => {
    const existingIndex = cartItems.value.findIndex(item => item.id === product.id)

    if (existingIndex >= 0) {
        const currentQuantity = cartItems.value[existingIndex].quantity
        if (currentQuantity < product.stock) {
            cartItems.value[existingIndex].quantity++
        } else {
            alert('Quantidade máxima em estoque atingida')
        }
    } else {
        cartItems.value.push({ ...product, quantity: 1 })
    }

    // Clear search after adding
    searchQuery.value = ''
    searchResults.value = []
}

const updateQuantity = (index: number, newQuantity: number) => {
    if (newQuantity <= 0) {
        removeFromCart(index)
        return
    }

    const item = cartItems.value[index]
    if (newQuantity <= item.stock) {
        cartItems.value[index].quantity = newQuantity
    } else {
        alert('Quantidade máxima em estoque atingida')
    }
}

const removeFromCart = (index: number) => {
    cartItems.value.splice(index, 1)
}

const clearCart = () => {
    cartItems.value = []
    form.value = {
        customer_name: '',
        payment_method: '',
        notes: '',
    }
}

const processSale = async () => {
    showValidation.value = true

    if (!form.value.payment_method) {
        return
    }

    if (cartItems.value.length === 0) {
        alert('Adicione pelo menos um item ao carrinho')
        return
    }

    processing.value = true

    try {
        const saleData = {
            customer_name: form.value.customer_name,
            payment_method: form.value.payment_method,
            notes: form.value.notes,
            items: cartItems.value.map(item => ({
                vinyl_sec_id: item.id,
                quantity: item.quantity,
                price: item.price,
                item_discount: 0,
            })),
        }

        const response = await fetch('/admin/pos', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify(saleData),
        })

        const result = await response.json()

        if (result.success) {
            lastSale.value = result.sale
            showSuccessModal.value = true
            showValidation.value = false
        } else {
            errorMessage.value = result.message || 'Erro ao processar venda'
            showError.value = true
            setTimeout(() => {
                showError.value = false
            }, 5000)
        }
    } catch (error) {
        console.error('Erro ao processar venda:', error)
        errorMessage.value = 'Erro ao processar venda. Verifique sua conexão e tente novamente.'
        showError.value = true
        setTimeout(() => {
            showError.value = false
        }, 5000)
    } finally {
        processing.value = false
    }
}

const printReceipt = async () => {
    if (lastSale.value) {
        try {
            const response = await fetch(`/admin/pos/${lastSale.value.id}/receipt`)
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
        }
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

        ${sale.items.map((item: any) => `
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

const newSale = () => {
    showSuccessModal.value = false
    lastSale.value = null
    clearCart()
}

// Keyboard shortcuts
const handleKeydown = (event: KeyboardEvent) => {
    // Ctrl/Cmd + Enter to finalize sale
    if ((event.ctrlKey || event.metaKey) && event.key === 'Enter') {
        event.preventDefault()
        if (cartItems.value.length > 0 && form.value.payment_method) {
            processSale()
        }
    }

    // Escape to clear cart
    if (event.key === 'Escape') {
        if (showSuccessModal.value) {
            newSale()
        } else if (cartItems.value.length > 0) {
            if (confirm('Deseja limpar o carrinho?')) {
                clearCart()
            }
        }
    }

    // F1 for help (focus search)
    if (event.key === 'F1') {
        event.preventDefault()
        const searchInput = document.querySelector('input[placeholder*="Buscar"]') as HTMLInputElement
        if (searchInput) {
            searchInput.focus()
        }
    }
}

onMounted(() => {
    document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown)
})
</script>
