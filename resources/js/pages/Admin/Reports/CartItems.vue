<template>
    <AdminLayout>
        <div class="p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Análise de Carrinhos Abandonados</h1>
                <p class="text-gray-600">Análise de carrinhos abandonados e potencial de recuperação</p>
            </div>

            <!-- Date Filter -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <form @submit.prevent="applyFilters" class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data Inicial</label>
                        <input v-model="filters.start_date" type="date"
                            class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data Final</label>
                        <input v-model="filters.end_date" type="date"
                            class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <button type="submit"
                        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                        Aplicar Filtros
                    </button>
                    <button type="button" @click="exportData"
                        class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors">
                        Exportar
                    </button>
                </form>
            </div>

            <!-- Cart Abandonment Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-yellow-100 rounded-lg">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 3H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13v4a2 2 0 01-2 2H9a2 2 0 01-2-2v-4m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01">
                                </path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total de Carrinhos</p>
                            <p class="text-2xl font-bold text-gray-900">{{ cartStats.total_carts }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-red-100 rounded-lg">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Carrinhos Abandonados</p>
                            <p class="text-2xl font-bold text-gray-900">{{ cartStats.abandoned_carts }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-orange-100 rounded-lg">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Taxa de Abandono</p>
                            <p class="text-2xl font-bold text-gray-900">{{ cartStats.abandonment_rate }}%</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                                </path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Valor Médio do Carrinho</p>
                            <p class="text-2xl font-bold text-gray-900">R$ {{
                                formatCurrency(cartStats.average_cart_value) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recovery Potential -->
            <div class="bg-white rounded-lg shadow mb-8">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Potencial de Recuperação</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <p class="text-sm font-medium text-gray-600">Carrinhos Recuperáveis</p>
                            <p class="text-2xl font-bold text-gray-900">{{ recoveryPotential.recoverable_carts }}</p>
                        </div>
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <p class="text-sm font-medium text-gray-600">Itens Abandonados</p>
                            <p class="text-2xl font-bold text-gray-900">{{ recoveryPotential.total_items }}</p>
                        </div>
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <p class="text-sm font-medium text-gray-600">Receita Potencial</p>
                            <p class="text-2xl font-bold text-green-600">R$ {{
                                formatCurrency(recoveryPotential.potential_revenue) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Most Abandoned Products -->
            <div class="bg-white rounded-lg shadow mb-8">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Produtos Mais Abandonados</h3>
                </div>
                <div class="p-6">
                    <div v-if="mostAbandonedProducts.length === 0" class="text-center text-gray-500 py-8">
                        Nenhum dado encontrado para o período selecionado
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Produto</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Abandonos</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Quantidade</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Preço Médio</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="product in mostAbandonedProducts" :key="product.title">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-full object-cover"
                                                    :src="product.cover_image || '/images/vinyl-placeholder.png'"
                                                    :alt="product.title">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ product.title }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ product.abandonment_count }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ product.total_quantity }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">R$ {{ formatCurrency(product.avg_price) }}
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Abandoned Carts List -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Carrinhos Abandonados</h3>
                </div>
                <div class="p-6">
                    <div v-if="abandonedCarts.data.length === 0" class="text-center text-gray-500 py-8">
                        Nenhum carrinho abandonado encontrado para o período selecionado
                    </div>
                    <div v-else>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Cliente</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Itens</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Última Atualização</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Valor Estimado</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="cart in abandonedCarts.data" :key="cart.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ cart.user?.name ||
                                                'Anônimo' }}</div>
                                            <div class="text-sm text-gray-500">{{ cart.user?.email || '-' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ cart.items.length }} itens</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ formatDate(cart.updated_at) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">R$ {{ calculateCartTotal(cart) }}</div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            <Pagination :links="abandonedCarts.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import Pagination from '@/components/Pagination.vue';

interface CartItem {
    id: number;
    product: {
        id: number;
        price: number;
    };
    quantity: number;
}

interface Cart {
    id: number;
    user: {
        name: string;
        email: string;
    } | null;
    items: CartItem[];
    updated_at: string;
}

interface AbandonedProduct {
    title: string;
    cover_image: string | null;
    abandonment_count: number;
    total_quantity: number;
    avg_price: number;
}

interface CartStats {
    total_carts: number;
    abandoned_carts: number;
    abandonment_rate: number;
    average_cart_value: number;
}

interface RecoveryPotential {
    potential_revenue: number;
    recoverable_carts: number;
    total_items: number;
}

interface Props {
    abandonedCarts: {
        data: Cart[];
        links: any;
    };
    cartStats: CartStats;
    mostAbandonedProducts: AbandonedProduct[];
    recoveryPotential: RecoveryPotential;
    dateRange: [string, string];
    filters: {
        start_date?: string;
        end_date?: string;
    };
}

const props = defineProps<Props>();

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

const calculateCartTotal = (cart: Cart) => {
    const total = cart.items.reduce((sum, item) => {
        return sum + (item.product?.price || 0) * item.quantity;
    }, 0);
    return formatCurrency(total);
};

const applyFilters = () => {
    router.get(route('admin.reports.cart-items'), filters, {
        preserveState: true,
        preserveScroll: true
    });
};

const exportData = () => {
    const params = new URLSearchParams(filters as any);
    params.append('export', 'true');
    window.open(`${route('admin.reports.cart-items')}?${params.toString()}`, '_blank');
};
</script>