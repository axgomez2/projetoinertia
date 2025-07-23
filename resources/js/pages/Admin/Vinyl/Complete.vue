<script setup>
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import { defineProps } from 'vue';

defineOptions({
    layout: AdminLayout
});

const props = defineProps({
    vinylMaster: Object,
    weights: Array,
    dimensions: Array,
    midiaStatuses: Array,
    coverStatuses: Array,
    suppliers: Array,
    categories: Array,
});

const form = useForm({
    weight_id: null,
    dimension_id: null,
    midia_status_id: null,
    cover_status_id: null,
    supplier_id: null,
    stock: 1,
    price: null,
    buy_price: null,
    promotional_price: null,
    is_new: false,
    in_stock: true, // Adicionado
    is_promotional: false,
    is_presale: false,
    presale_arrival_date: null,
    categories: [],
});

const submitComplete = () => {
    form.post(route('admin.vinyls.completeStore', props.vinylMaster.id));
};

const goBack = () => {
    router.visit(route('admin.vinyls.index'));
};
</script>

<template>
  <div>
    <h1 class="mb-8 text-3xl font-bold text-gray-800">Finalizar Cadastro do Disco</h1>

    <!-- Informações do Disco para Conferência -->
    <div class="bg-white rounded-md shadow p-6 mb-6">
      <h2 class="text-xl font-bold mb-4 text-gray-800">Disco Cadastrado</h2>
      <div class="flex items-center space-x-4">
        <img :src="vinylMaster.cover_image || '/images/placeholder.png'" 
             alt="Capa" 
             class="w-20 h-20 object-cover rounded-lg shadow-md">
        <div>
          <h3 class="text-lg font-semibold text-gray-800">{{ vinylMaster.title }}</h3>
          <p class="text-gray-600">{{ vinylMaster.release_year }} - {{ vinylMaster.country }}</p>
          <p class="text-sm text-gray-500">
            Artistas: {{ vinylMaster.artists.map(a => a.name).join(', ') }}
          </p>
        </div>
      </div>
    </div>

    <!-- Formulário de Finalização -->
    <div class="bg-white rounded-md shadow p-8">
      <h2 class="text-xl font-bold mb-6 text-gray-800">Informações de Estoque e Preço</h2>
      
      <form @submit.prevent="submitComplete">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          
          <!-- Coluna 1: Informações Físicas -->
          <div class="space-y-6">
            <h3 class="font-semibold text-gray-700 border-b pb-2">Informações Físicas</h3>
            
            <!-- Peso -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Peso</label>
              <select v-model="form.weight_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Selecione o peso</option>
                <option v-for="weight in weights" :key="weight.id" :value="weight.id">
                  {{ weight.name }}
                </option>
              </select>
            </div>

            <!-- Dimensão -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Dimensão</label>
              <select v-model="form.dimension_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Selecione a dimensão</option>
                <option v-for="dimension in dimensions" :key="dimension.id" :value="dimension.id">
                  {{ dimension.name }}
                </option>
              </select>
            </div>

            <!-- Status da Mídia -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Status da Mídia</label>
              <select v-model="form.midia_status_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Selecione o status da mídia</option>
                <option v-for="status in midiaStatuses" :key="status.id" :value="status.id">
                  {{ status.title }}
                </option>
              </select>
            </div>

            <!-- Status da Capa -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Status da Capa</label>
              <select v-model="form.cover_status_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Selecione o status da capa</option>
                <option v-for="status in coverStatuses" :key="status.id" :value="status.id">
                  {{ status.title }}
                </option>
              </select>
            </div>

            <!-- Fornecedor -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Fornecedor</label>
              <select v-model="form.supplier_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Selecione o fornecedor</option>
                <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                  {{ supplier.name }}
                </option>
              </select>
            </div>
          </div>

          <!-- Coluna 2: Preços e Estoque -->
          <div class="space-y-6">
            <h3 class="font-semibold text-gray-700 border-b pb-2">Preços e Estoque</h3>
            
            <!-- Estoque -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Quantidade em Estoque *</label>
              <input 
                v-model="form.stock" 
                type="number" 
                min="0" 
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="0">
            </div>

            <!-- Preço de Venda -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Preço de Venda *</label>
              <input 
                v-model="form.price" 
                type="number" 
                step="0.01" 
                min="0" 
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="0.00">
            </div>

            <!-- Preço de Compra -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Preço de Compra</label>
              <input 
                v-model="form.buy_price" 
                type="number" 
                step="0.01" 
                min="0"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="0.00">
            </div>

            <!-- Preço Promocional -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Preço Promocional</label>
              <input 
                v-model="form.promotional_price" 
                type="number" 
                step="0.01" 
                min="0"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="0.00">
            </div>

            <!-- Data de Chegada (Pré-venda) -->
            <div v-if="form.is_presale">
              <label class="block text-sm font-medium text-gray-700 mb-2">Data de Chegada (Pré-venda)</label>
              <input 
                v-model="form.presale_arrival_date" 
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
            </div>
          </div>
        </div>

        <!-- Categorias -->
        <div class="mt-8">
          <h3 class="font-semibold text-gray-700 border-b pb-2 mb-4">Categorias</h3>
          <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
            <label v-for="category in categories" :key="category.id" class="flex items-center space-x-2 cursor-pointer">
              <input 
                type="checkbox" 
                :value="category.id" 
                v-model="form.categories"
                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
              <span class="text-sm text-gray-700">{{ category.name }}</span>
            </label>
          </div>
        </div>

        <!-- Opções Booleanas -->
        <div class="mt-8 grid grid-cols-2 md:grid-cols-4 gap-4">
          <label class="flex items-center space-x-2 cursor-pointer">
            <input 
              type="checkbox" 
              v-model="form.is_new"
              class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <span class="text-sm text-gray-700">Novo</span>
          </label>

          <label class="flex items-center space-x-2 cursor-pointer">
            <input 
              type="checkbox" 
              v-model="form.in_stock"
              class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <span class="text-sm text-gray-700">Em Estoque</span>
          </label>

          <label class="flex items-center space-x-2 cursor-pointer">
            <input 
              type="checkbox" 
              v-model="form.is_promotional"
              class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <span class="text-sm text-gray-700">Promocional</span>
          </label>

          <label class="flex items-center space-x-2 cursor-pointer">
            <input 
              type="checkbox" 
              v-model="form.is_presale"
              class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <span class="text-sm text-gray-700">Pré-venda</span>
          </label>
        </div>

        <!-- Botões -->
        <div class="mt-8 flex justify-end space-x-4">
          <button 
            type="button" 
            @click="goBack"
            class="px-6 py-3 bg-gray-500 text-white font-bold rounded-md hover:bg-gray-600">
            Voltar
          </button>
          <button 
            type="submit" 
            :disabled="form.processing"
            class="px-6 py-3 bg-indigo-600 text-white font-bold rounded-md hover:bg-indigo-500 disabled:opacity-50">
            <span v-if="form.processing">Finalizando...</span>
            <span v-else>Finalizar Cadastro</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
