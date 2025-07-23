<template>
  <div class="fixed left-0 top-0 w-64 h-full bg-white shadow-md z-10">
    <div class="p-4">
      <h2 class="text-xl font-bold text-gray-800">painel</h2>
    </div>
    <nav class="mt-4">
      <Link :href="route('admin.dashboard')" class="block py-2.5 px-4 rounded transition duration-200 text-gray-800 hover:bg-gray-200" :class="{'bg-gray-300': $page.component === 'Admin/Dashboard'}">Dashboard</Link>
      <Link :href="route('admin.vinyls.index')" class="block py-2.5 px-4 rounded transition duration-200 text-gray-800 hover:bg-gray-200" :class="{'bg-gray-300': $page.url.startsWith('/admin/vinyls')}">Discos</Link>
      <Link :href="route('admin.orders.online.index')" class="block py-2.5 px-4 rounded transition duration-200 text-gray-800 hover:bg-gray-200" :class="{'bg-gray-300': $page.url.startsWith('/admin/orders')}">Pedidos Online</Link>
      <Link :href="route('admin.pos.index')" class="block py-2.5 px-4 rounded transition duration-200 text-gray-800 hover:bg-gray-200" :class="{'bg-gray-300': $page.url.startsWith('/admin/pos')}">PDV (Vendas)</Link>
      <Link :href="route('admin.customers.index')" class="block py-2.5 px-4 rounded transition duration-200 text-gray-800 hover:bg-gray-200" :class="{'bg-gray-300': $page.url.startsWith('/admin/customers')}">Clientes</Link>

      <!-- Reports Section -->
      <div class="mt-4 pt-4 border-t border-gray-200">
        <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Relatórios</h3>
        <Link :href="route('admin.reports.index')" class="block py-2.5 px-4 rounded transition duration-200 text-gray-800 hover:bg-gray-200" :class="{'bg-gray-300': $page.component === 'Admin/Reports/Dashboard'}">Dashboard</Link>
        <Link :href="route('admin.reports.vinyls')" class="block py-2.5 px-4 rounded transition duration-200 text-gray-800 hover:bg-gray-200" :class="{'bg-gray-300': $page.component === 'Admin/Reports/Vinyls'}">Vinis</Link>
        <Link :href="route('admin.reports.cart-items')" class="block py-2.5 px-4 rounded transition duration-200 text-gray-800 hover:bg-gray-200" :class="{'bg-gray-300': $page.component === 'Admin/Reports/CartItems'}">Carrinhos</Link>
        <Link :href="route('admin.reports.wishlist')" class="block py-2.5 px-4 rounded transition duration-200 text-gray-800 hover:bg-gray-200" :class="{'bg-gray-300': $page.component === 'Admin/Reports/Wishlist'}">Lista de Desejos</Link>
        <Link :href="route('admin.reports.wantlist')" class="block py-2.5 px-4 rounded transition duration-200 text-gray-800 hover:bg-gray-200" :class="{'bg-gray-300': $page.component === 'Admin/Reports/Wantlist'}">Lista de Procurados</Link>
        <Link :href="route('admin.reports.product-views')" class="block py-2.5 px-4 rounded transition duration-200 text-gray-800 hover:bg-gray-200" :class="{'bg-gray-300': $page.component === 'Admin/Reports/ProductViews'}">Visualizações</Link>
        <Link :href="route('admin.reports.supplier-inventory')" class="block py-2.5 px-4 rounded transition duration-200 text-gray-800 hover:bg-gray-200" :class="{'bg-gray-300': $page.component === 'Admin/Reports/SupplierInventory'}">Inventário por Fornecedor</Link>
      </div>

      <!-- Settings Section -->
      <div class="mt-4 pt-4 border-t border-gray-200">
        <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Configurações</h3>
        <Link :href="route('admin.settings.categories')" class="block py-2.5 px-4 rounded transition duration-200 text-gray-800 hover:bg-gray-200" :class="{'bg-gray-300': $page.component === 'Admin/Settings/Categories'}">Categorias</Link>
        <Link :href="route('admin.settings.media-status')" class="block py-2.5 px-4 rounded transition duration-200 text-gray-800 hover:bg-gray-200" :class="{'bg-gray-300': $page.component === 'Admin/Settings/MediaStatus'}">Status da Mídia</Link>
        <Link :href="route('admin.settings.cover-status')" class="block py-2.5 px-4 rounded transition duration-200 text-gray-800 hover:bg-gray-200" :class="{'bg-gray-300': $page.component === 'Admin/Settings/CoverStatus'}">Status da Capa</Link>
        <Link :href="route('admin.settings.suppliers')" class="block py-2.5 px-4 rounded transition duration-200 text-gray-800 hover:bg-gray-200" :class="{'bg-gray-300': $page.component === 'Admin/Settings/Suppliers'}">Fornecedores</Link>
      </div>
    </nav>
  </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>
