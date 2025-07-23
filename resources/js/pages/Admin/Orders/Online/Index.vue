<template>
    <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-semibold text-gray-900">Pedidos Online</h2>
                            <div class="flex space-x-2">
                                <button
                                    @click="exportOrders"
                                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium"
                                >
                                    Exportar CSV
                                </button>
                            </div>
                        </div>

                        <!-- Enhanced Filters -->
                        <div class="mb-6">
                            <!-- Search and Quick Actions Row -->
                            <div class="flex flex-col sm:flex-row gap-4 mb-4">
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Buscar Pedidos</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                        </div>
                                        <input
                                            v-model="filters.search"
                                            type="text"
                                            placeholder="Número do pedido, nome do cliente, email..."
                                            class="pl-10 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            @input="debounceSearch"
                                        />
                                        <div v-if="filters.search" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                            <button
                                                @click="clearSearch"
                                                class="text-gray-400 hover:text-gray-600"
                                            >
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <button
                                        @click="clearAllFilters"
                                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm font-medium flex items-center"
                                    >
                                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        Limpar Filtros
                                    </button>
                                </div>
                            </div>

                            <!-- Filter Options Row -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Status do Pedido</label>
                                    <select
                                        v-model="filters.status"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        @change="applyFilters"
                                    >
                                        <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                                            {{ option.label }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Status do Pagamento</label>
                                    <select
                                        v-model="filters.payment_status"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        @change="applyFilters"
                                    >
                                        <option v-for="option in paymentStatusOptions" :key="option.value" :value="option.value">
                                            {{ option.label }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
                                    <select
                                        v-model="filters.customer_id"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        @change="applyFilters"
                                    >
                                        <option v-for="customer in customers" :key="customer.value" :value="customer.value">
                                            {{ customer.label }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Período</label>
                                    <select
                                        v-model="filters.date_range"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        @change="handleDateRangeChange"
                                    >
                                        <option value="">Todos os períodos</option>
                                        <option value="today">Hoje</option>
                                        <option value="yesterday">Ontem</option>
                                        <option value="this_week">Esta semana</option>
                                        <option value="last_week">Semana passada</option>
                                        <option value="this_month">Este mês</option>
                                        <option value="last_month">Mês passado</option>
                                        <option value="custom">Período personalizado</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Custom Date Range (shown when custom is selected) -->
                            <div v-if="filters.date_range === 'custom'" class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Data Início</label>
                                    <input
                                        v-model="filters.start_date"
                                        type="date"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        @change="applyFilters"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Data Fim</label>
                                    <input
                                        v-model="filters.end_date"
                                        type="date"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        @change="applyFilters"
                                    />
                                </div>
                            </div>

                            <!-- Active Filters Display -->
                            <div v-if="hasActiveFilters" class="mt-4">
                                <div class="flex flex-wrap gap-2">
                                    <span class="text-sm text-gray-600">Filtros ativos:</span>
                                    <span v-if="filters.status" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Status: {{ getStatusLabel(filters.status) }}
                                        <button @click="clearFilter('status')" class="ml-1 text-blue-600 hover:text-blue-800">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </span>
                                    <span v-if="filters.payment_status" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Pagamento: {{ getPaymentStatusLabel(filters.payment_status) }}
                                        <button @click="clearFilter('payment_status')" class="ml-1 text-green-600 hover:text-green-800">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </span>
                                    <span v-if="filters.customer_id" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        Cliente: {{ getCustomerLabel(filters.customer_id) }}
                                        <button @click="clearFilter('customer_id')" class="ml-1 text-purple-600 hover:text-purple-800">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </span>
                                    <span v-if="filters.date_range && filters.date_range !== 'custom'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Período: {{ getDateRangeLabel(filters.date_range) }}
                                        <button @click="clearFilter('date_range')" class="ml-1 text-yellow-600 hover:text-yellow-800">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </span>
                                    <span v-if="filters.start_date && filters.end_date" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        {{ formatDate(filters.start_date) }} - {{ formatDate(filters.end_date) }}
                                        <button @click="clearDateRange" class="ml-1 text-yellow-600 hover:text-yellow-800">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Orders Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Pedido
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Cliente
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
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
                                    <tr v-for="order in orders.data" :key="order.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ order.order_number }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ order.customer_name }}</div>
                                            <div class="text-sm text-gray-500">{{ order.customer_email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="getStatusClass(order.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                                {{ order.status_label }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="getPaymentStatusClass(order.payment_status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                                {{ order.payment_status_label }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            R$ {{ formatCurrency(order.total_amount) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatDate(order.created_at) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link
                                                :href="route('admin.orders.online.show', order.id)"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                Ver Detalhes
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            <Pagination :links="orders.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
</template>

<script setup lang="ts">
import { reactive, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/admin/AdminLayout.vue'
import Pagination from '@/components/Pagination.vue'

defineOptions({
    layout: AdminLayout,
})

const props = defineProps({
    orders: Object,
    filters: Object,
    statusOptions: Array,
    paymentStatusOptions: Array,
    customers: Array,
})

const filters = reactive({
    search: props.filters.search || '',
    status: props.filters.status || '',
    payment_status: props.filters.payment_status || '',
    customer_id: props.filters.customer_id || '',
    date_range: props.filters.date_range || '',
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
})

let searchTimeout = null

const debounceSearch = () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        applyFilters()
    }, 500)
}

const applyFilters = () => {
    router.get(route('admin.orders.online.index'), filters, {
        preserveState: true,
        replace: true,
    })
}

const exportOrders = () => {
    const params = new URLSearchParams(filters)
    window.open(route('admin.orders.online.export') + '?' + params.toString())
}

const getStatusClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        processing: 'bg-blue-100 text-blue-800',
        shipped: 'bg-purple-100 text-purple-800',
        delivered: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800',
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

const getPaymentStatusClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        paid: 'bg-green-100 text-green-800',
        failed: 'bg-red-100 text-red-800',
        refunded: 'bg-gray-100 text-gray-800',
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value)
}

const formatDate = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

// Enhanced filtering methods
const clearSearch = () => {
    filters.search = ''
    applyFilters()
}

const clearFilter = (filterName) => {
    filters[filterName] = ''
    if (filterName === 'date_range') {
        filters.start_date = ''
        filters.end_date = ''
    }
    applyFilters()
}

const clearDateRange = () => {
    filters.start_date = ''
    filters.end_date = ''
    filters.date_range = ''
    applyFilters()
}

const clearAllFilters = () => {
    Object.keys(filters).forEach(key => {
        filters[key] = ''
    })
    applyFilters()
}

const hasActiveFilters = computed(() => {
    return filters.search || filters.status || filters.payment_status ||
           filters.customer_id || filters.date_range ||
           (filters.start_date && filters.end_date)
})

const getStatusLabel = (status) => {
    const option = props.statusOptions.find(opt => opt.value === status)
    return option ? option.label : status
}

const getPaymentStatusLabel = (status) => {
    const option = props.paymentStatusOptions.find(opt => opt.value === status)
    return option ? option.label : status
}

const getCustomerLabel = (customerId) => {
    const customer = props.customers.find(c => c.value === customerId)
    return customer ? customer.label : customerId
}

const getDateRangeLabel = (range) => {
    const labels = {
        today: 'Hoje',
        yesterday: 'Ontem',
        this_week: 'Esta semana',
        last_week: 'Semana passada',
        this_month: 'Este mês',
        last_month: 'Mês passado'
    }
    return labels[range] || range
}

const handleDateRangeChange = () => {
    if (filters.date_range !== 'custom') {
        filters.start_date = ''
        filters.end_date = ''
    }
    applyFilters()
}
</script>
