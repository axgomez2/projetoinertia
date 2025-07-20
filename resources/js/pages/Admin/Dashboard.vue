<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">Dashboard</h1>
    <div class="bg-white p-6 rounded-lg shadow-md">
      <p>Bem-vindo ao painel de administração!</p>
    </div>
  </div>
</template>

<script setup>
import AdminLayout from '@/layouts/admin/AdminLayout.vue';

defineOptions({
  layout: AdminLayout,
});
</script>
