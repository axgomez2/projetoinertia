<template>
    <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h2 class="text-2xl font-semibold text-gray-900">
                                    Pedido #{{ order.order_number }}
                                </h2>
                                <p class="text-sm text-gray-600 mt-1">
                                    Criado em {{ formatDate(order.created_at) }}
                                </p>
                            </div>
                            <div class="flex space-x-2">
                                <Link
                                    :href="route('admin.orders.online.index')"
                                    class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium"
                                >
                                    Voltar
                                </Link>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- Order Status and Info -->
                            <div class="lg:col-span-2 space-y-6">
                                <!-- Order Status -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Status do Pedido</h3>
                                    <div class="flex items-center space-x-4">
                                        <span :class="getStatusClass(order.status)" class="px-3 py-1 text-sm font-semibold rounded-full">
                                            {{ order.status_label }}
                                        </span>
                                        <span :class="getPaymentStatusClass(order.payment_status)" class="px-3 py-1 text-sm font-semibold rounded-full">
                                            Pagamento: {{ order.payment_status_label }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Order Items -->
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Itens do Pedido</h3>
                                    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
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
                                                        Qtd
                                                    </th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Total
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                <tr v-for="item in order.items" :key="item.id">
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ item.product_name }}
                                                        </div>
                                                        <div class="text-sm text-gray-500" v-if="item.product">
                                                            ID: {{ item.product.id }}
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        R$ {{ formatCurrency(item.price) }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {{ item.quantity }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        R$ {{ formatCurrency(item.item_total) }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Order Status Timeline -->
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Timeline do Pedido</h3>
                                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                                        <div class="flow-root">
                                            <ul class="-mb-8">
                                                <li v-for="(event, eventIdx) in orderTimeline" :key="event.id">
                                                    <div class="relative pb-8">
                                                        <span v-if="eventIdx !== orderTimeline.length - 1" class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                                        <div class="relative flex space-x-3">
                                                            <div>
                                                                <span :class="[event.iconBackground, 'h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white']">
                                                                    <component :is="event.icon" :class="[event.iconForeground, 'h-5 w-5']" aria-hidden="true" />
                                                                </span>
                                                            </div>
                                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                                <div>
                                                                    <p class="text-sm text-gray-900">
                                                                        {{ event.content }}
                                                                        <span v-if="event.target" class="font-medium text-gray-900">{{ event.target }}</span>
                                                                    </p>
                                                                    <p v-if="event.notes" class="text-sm text-gray-600 mt-1">{{ event.notes }}</p>
                                                                    <p v-if="event.user" class="text-xs text-gray-500 mt-1">por {{ event.user }}</p>
                                                                </div>
                                                                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                                    <time :datetime="event.datetime">{{ event.date }}</time>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Order Summary and Customer Info -->
                            <div class="space-y-6">
                                <!-- Order Summary -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Resumo do Pedido</h3>
                                    <div class="space-y-2">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Subtotal:</span>
                                            <span class="text-gray-900">R$ {{ formatCurrency(order.subtotal) }}</span>
                                        </div>
                                        <div class="flex justify-between text-sm" v-if="order.tax_amount > 0">
                                            <span class="text-gray-600">Taxa:</span>
                                            <span class="text-gray-900">R$ {{ formatCurrency(order.tax_amount) }}</span>
                                        </div>
                                        <div class="flex justify-between text-sm" v-if="order.shipping_amount > 0">
                                            <span class="text-gray-600">Frete:</span>
                                            <span class="text-gray-900">R$ {{ formatCurrency(order.shipping_amount) }}</span>
                                        </div>
                                        <div class="flex justify-between text-sm" v-if="order.discount_amount > 0">
                                            <span class="text-gray-600">Desconto:</span>
                                            <span class="text-red-600">-R$ {{ formatCurrency(order.discount_amount) }}</span>
                                        </div>
                                        <div class="border-t pt-2 mt-2">
                                            <div class="flex justify-between text-base font-medium">
                                                <span class="text-gray-900">Total:</span>
                                                <span class="text-gray-900">R$ {{ formatCurrency(order.total_amount) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Customer Information -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Informações do Cliente</h3>
                                    <div class="space-y-2">
                                        <div>
                                            <span class="text-sm font-medium text-gray-600">Nome:</span>
                                            <p class="text-sm text-gray-900">{{ order.customer_name }}</p>
                                        </div>
                                        <div>
                                            <span class="text-sm font-medium text-gray-600">Email:</span>
                                            <p class="text-sm text-gray-900">{{ order.customer_email }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payment Information -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Informações de Pagamento</h3>
                                    <div class="space-y-2">
                                        <div>
                                            <span class="text-sm font-medium text-gray-600">Método:</span>
                                            <p class="text-sm text-gray-900">{{ order.payment_method || 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-sm font-medium text-gray-600">Status:</span>
                                            <span :class="getPaymentStatusClass(order.payment_status)" class="px-2 py-1 text-xs font-semibold rounded-full ml-2">
                                                {{ order.payment_status_label }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Shipping Address -->
                                <div class="bg-gray-50 p-4 rounded-lg" v-if="order.shipping_address">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Endereço de Entrega</h3>
                                    <div class="text-sm text-gray-900">
                                        <p>{{ order.shipping_address.first_name }} {{ order.shipping_address.last_name }}</p>
                                        <p v-if="order.shipping_address.company">{{ order.shipping_address.company }}</p>
                                        <p>{{ order.shipping_address.address_line_1 }}</p>
                                        <p v-if="order.shipping_address.address_line_2">{{ order.shipping_address.address_line_2 }}</p>
                                        <p>{{ order.shipping_address.city }}, {{ order.shipping_address.state }} {{ order.shipping_address.postal_code }}</p>
                                        <p>{{ order.shipping_address.country }}</p>
                                        <p v-if="order.shipping_address.phone">Tel: {{ order.shipping_address.phone }}</p>
                                    </div>
                                </div>

                                <!-- Billing Address -->
                                <div class="bg-gray-50 p-4 rounded-lg" v-if="order.billing_address">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Endereço de Cobrança</h3>
                                    <div class="text-sm text-gray-900">
                                        <p>{{ order.billing_address.first_name }} {{ order.billing_address.last_name }}</p>
                                        <p v-if="order.billing_address.company">{{ order.billing_address.company }}</p>
                                        <p>{{ order.billing_address.address_line_1 }}</p>
                                        <p v-if="order.billing_address.address_line_2">{{ order.billing_address.address_line_2 }}</p>
                                        <p>{{ order.billing_address.city }}, {{ order.billing_address.state }} {{ order.billing_address.postal_code }}</p>
                                        <p>{{ order.billing_address.country }}</p>
                                        <p v-if="order.billing_address.phone">Tel: {{ order.billing_address.phone }}</p>
                                    </div>
                                </div>

                                <!-- Order Notes -->
                                <div class="bg-gray-50 p-4 rounded-lg" v-if="order.notes">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Observações</h3>
                                    <p class="text-sm text-gray-900">{{ order.notes }}</p>
                                </div>

                                <!-- Order Dates -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Datas Importantes</h3>
                                    <div class="space-y-2">
                                        <div>
                                            <span class="text-sm font-medium text-gray-600">Criado:</span>
                                            <p class="text-sm text-gray-900">{{ formatDate(order.created_at) }}</p>
                                        </div>
                                        <div v-if="order.processed_at">
                                            <span class="text-sm font-medium text-gray-600">Processado:</span>
                                            <p class="text-sm text-gray-900">{{ formatDate(order.processed_at) }}</p>
                                        </div>
                                        <div v-if="order.shipped_at">
                                            <span class="text-sm font-medium text-gray-600">Enviado:</span>
                                            <p class="text-sm text-gray-900">{{ formatDate(order.shipped_at) }}</p>
                                        </div>
                                        <div v-if="order.delivered_at">
                                            <span class="text-sm font-medium text-gray-600">Entregue:</span>
                                            <p class="text-sm text-gray-900">{{ formatDate(order.delivered_at) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { computed, h } from 'vue'
import AdminLayout from '@/layouts/admin/AdminLayout.vue'

defineOptions({
    layout: AdminLayout,
})

const props = defineProps({
    order: Object,
})

// Timeline icons (using simple SVG paths as components)
const CheckCircleIcon = () => h('svg', { fill: 'currentColor', viewBox: '0 0 20 20' }, [
    h('path', { 'fill-rule': 'evenodd', d: 'M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z', 'clip-rule': 'evenodd' })
])

const ClockIcon = () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' })
])

const TruckIcon = () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' })
])

const XCircleIcon = () => h('svg', { fill: 'currentColor', viewBox: '0 0 20 20' }, [
    h('path', { 'fill-rule': 'evenodd', d: 'M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z', 'clip-rule': 'evenodd' })
])

const CreditCardIcon = () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z' })
])

const ShoppingCartIcon = () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01' })
])

// Computed property for order timeline
const orderTimeline = computed(() => {
    const timeline = []

    // Order created
    timeline.push({
        id: 'created',
        content: 'Pedido criado',
        target: `#${props.order.order_number}`,
        datetime: props.order.created_at,
        date: formatDate(props.order.created_at),
        icon: ShoppingCartIcon,
        iconBackground: 'bg-blue-500',
        iconForeground: 'text-white',
        notes: props.order.notes
    })

    // Add status history events
    if (props.order.status_history && props.order.status_history.length > 0) {
        props.order.status_history.forEach(history => {
            timeline.push({
                id: `status-${history.id}`,
                content: `Status alterado para ${history.status_label}`,
                target: history.previous_status ? `(anterior: ${history.previous_status})` : '',
                datetime: history.created_at,
                date: formatDate(history.created_at),
                icon: getStatusIcon(history.status),
                iconBackground: getStatusIconBackground(history.status),
                iconForeground: 'text-white',
                notes: history.notes,
                user: history.changed_by?.name
            })
        })
    }

    // Add payment events
    if (props.order.payment_status === 'paid') {
        timeline.push({
            id: 'payment',
            content: 'Pagamento confirmado',
            target: props.order.payment_method,
            datetime: props.order.updated_at, // Assuming payment was updated when status changed
            date: formatDate(props.order.updated_at),
            icon: CreditCardIcon,
            iconBackground: 'bg-green-500',
            iconForeground: 'text-white'
        })
    }

    // Add processing event
    if (props.order.processed_at) {
        timeline.push({
            id: 'processed',
            content: 'Pedido processado',
            datetime: props.order.processed_at,
            date: formatDate(props.order.processed_at),
            icon: ClockIcon,
            iconBackground: 'bg-yellow-500',
            iconForeground: 'text-white'
        })
    }

    // Add shipping event
    if (props.order.shipped_at) {
        timeline.push({
            id: 'shipped',
            content: 'Pedido enviado',
            datetime: props.order.shipped_at,
            date: formatDate(props.order.shipped_at),
            icon: TruckIcon,
            iconBackground: 'bg-purple-500',
            iconForeground: 'text-white'
        })
    }

    // Add delivery event
    if (props.order.delivered_at) {
        timeline.push({
            id: 'delivered',
            content: 'Pedido entregue',
            datetime: props.order.delivered_at,
            date: formatDate(props.order.delivered_at),
            icon: CheckCircleIcon,
            iconBackground: 'bg-green-500',
            iconForeground: 'text-white'
        })
    }

    // Sort by date (most recent first)
    return timeline.sort((a, b) => new Date(b.datetime) - new Date(a.datetime))
})

const getStatusIcon = (status) => {
    switch (status) {
        case 'pending':
            return ClockIcon
        case 'processing':
            return ClockIcon
        case 'shipped':
            return TruckIcon
        case 'delivered':
            return CheckCircleIcon
        case 'cancelled':
            return XCircleIcon
        default:
            return ClockIcon
    }
}

const getStatusIconBackground = (status) => {
    switch (status) {
        case 'pending':
            return 'bg-yellow-500'
        case 'processing':
            return 'bg-blue-500'
        case 'shipped':
            return 'bg-purple-500'
        case 'delivered':
            return 'bg-green-500'
        case 'cancelled':
            return 'bg-red-500'
        default:
            return 'bg-gray-500'
    }
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
    return new Date(date).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}
</script>
