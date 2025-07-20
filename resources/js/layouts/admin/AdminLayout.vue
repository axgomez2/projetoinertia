<template>
  <div class="min-h-screen bg-gray-100">
    <Sidebar />
    <div class="ml-64">
      <Topnav />
      <main class="p-8">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import Sidebar from '@/layouts/admin/Sidebar.vue';
import Topnav from '@/layouts/admin/Topnav.vue';
</script>
