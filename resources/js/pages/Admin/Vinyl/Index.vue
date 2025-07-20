<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl text-gray-700 font-bold">Discos de Vinil</h1>
      <Link 
        :href="route('admin.vinyls.create')"
        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition"
      >
        Adicionar Novo Disco
      </Link>
    </div>

    <!-- Filtros -->
    <div class="bg-white p-4 rounded-lg shadow-md mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="text" placeholder="Buscar por título, artista..." class="sm:text-sm rounded-md text-gray-700 border-gray-300">
            <select class="sm:text-sm rounded-md text-gray-700 border-gray-300">
                <option value="">Todas as Categorias</option>
                <!-- Popular com categorias reais depois -->
            </select>
            <select class="sm:text-sm rounded-md text-gray-700 border-gray-300">
                <option value="">Toda a Disponibilidade</option>
                <option value="1">Disponível</option>
                <option value="0">Indisponível</option>
            </select>
            <button class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Filtrar</button>
        </div>
    </div>

    <!-- Tabela de Discos -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capa</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Artistas</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ano</th>
            <th scope="col" class="relative px-6 py-3">
              <span class="sr-only">Ações</span>
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="vinyl in vinyls.data" :key="vinyl.id">
            <td class="px-6 py-4 whitespace-nowrap">
              <img :src="vinyl.cover_image || '/images/placeholder.png'" alt="Capa" class="w-12 h-12 object-cover rounded">
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">{{ vinyl.title }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
              <span v-if="vinyl.artists && vinyl.artists.length > 0">
                {{ vinyl.artists.map(artist => artist.name).join(', ') }}
              </span>
              <span v-else class="text-gray-400">Sem artista</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ vinyl.release_year }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <Link :href="route('admin.vinyls.edit', vinyl.id)" class="text-indigo-600 hover:text-indigo-900">Editar</Link>
              <!-- O link de exclusão será implementado depois -->
            </td>
          </tr>
          <tr v-if="vinyls.data.length === 0">
              <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Nenhum disco encontrado.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Paginação -->
    <div v-if="vinyls.links.length > 3" class="mt-6">
        <div class="flex flex-wrap -mb-1">
            <template v-for="(link, key) in vinyls.links" :key="key">
                <div v-if="link.url === null" class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border rounded" v-html="link.label" />
                <Link v-else class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded hover:bg-white focus:border-indigo-500 focus:text-indigo-500" :class="{ 'bg-white': link.active }" :href="link.url" v-html="link.label" />
            </template>
        </div>
    </div>

  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';

defineProps({
  vinyls: Object,
});

defineOptions({
  layout: AdminLayout,
});

</script>
