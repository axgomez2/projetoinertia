<template>
  <div class="fixed left-0 top-0 w-64 h-full bg-white shadow-md z-10">
    <div class="p-4">
      <h2 class="text-xl font-bold text-gray-800">painel</h2>
    </div>
    <nav class="mt-4">
      <Link :href="route('admin.dashboard')" class="block py-2.5 px-4 rounded transition duration-200 text-gray-800 hover:bg-gray-200" :class="{'bg-gray-300': $page.component === 'Admin/Dashboard'}">Dashboard</Link>
      <Link :href="route('admin.vinyls.index')" class="block py-2.5 px-4 rounded transition duration-200 text-gray-800 hover:bg-gray-200" :class="{'bg-gray-300': $page.url.startsWith('/admin/vinyls')}">Discos</Link>
      <a href="#" class="block py-2.5 px-4 rounded transition duration-200 text-gray-800 hover:bg-gray-200">Pedidos</a>
      <a href="#" class="block py-2.5 px-4 rounded transition duration-200 text-gray-800 hover:bg-gray-200">Clientes</a>
    </nav>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { Home, Disc } from 'lucide-vue-next';
</script>
