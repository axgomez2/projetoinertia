<script setup lang="ts">
import { ref } from 'vue';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';

defineOptions({
  layout: AdminLayout,
});

// Mock data for testing
const categories = ref([
  { id: 1, name: 'Rock', slug: 'rock' },
  { id: 2, name: 'Jazz', slug: 'jazz' },
  { id: 3, name: 'Classical', slug: 'classical' }
]);

const mediaStatuses = ref([
  { id: 1, title: 'New', description: 'Brand new media' },
  { id: 2, title: 'Used', description: 'Used but in good condition' },
  { id: 3, title: 'Damaged', description: 'Has some damage' }
]);

const coverStatuses = ref([
  { id: 1, title: 'Perfect', description: 'No damage or wear' },
  { id: 2, title: 'Good', description: 'Minor wear but no damage' },
  { id: 3, title: 'Fair', description: 'Some visible wear' }
]);

const suppliers = ref([
  {
    id: 1,
    name: 'Supplier 1',
    email: 'supplier1@example.com',
    phone: '123-456-7890',
    address: '123 Main St',
    city: 'New York',
    state: 'NY',
    zipcode: '10001',
    document: '123456789',
    document_type: 'cnpj',
    website: 'https://supplier1.com',
    notes: 'Good supplier'
  },
  {
    id: 2,
    name: 'Supplier 2',
    email: 'supplier2@example.com',
    phone: '987-654-3210',
    address: '456 Oak St',
    city: 'Los Angeles',
    state: 'CA',
    zipcode: '90001',
    document: '987654321',
    document_type: 'cpf',
    website: 'https://supplier2.com',
    notes: 'Reliable supplier'
  }
]);
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold mb-6">Test Page</h1>

    <div class="grid grid-cols-2 gap-6">
      <div>
        <h2 class="text-xl font-semibold mb-4">Categories</h2>
        <div class="bg-white p-4 rounded shadow">
          <ul class="space-y-2">
            <li v-for="category in categories" :key="category.id" class="border-b pb-2">
              <div class="font-medium">{{ category.name }}</div>
              <div class="text-sm text-gray-500">{{ category.slug }}</div>
            </li>
          </ul>
        </div>
      </div>

      <div>
        <h2 class="text-xl font-semibold mb-4">Media Statuses</h2>
        <div class="bg-white p-4 rounded shadow">
          <ul class="space-y-2">
            <li v-for="status in mediaStatuses" :key="status.id" class="border-b pb-2">
              <div class="font-medium">{{ status.title }}</div>
              <div class="text-sm text-gray-500">{{ status.description }}</div>
            </li>
          </ul>
        </div>
      </div>

      <div>
        <h2 class="text-xl font-semibold mb-4">Cover Statuses</h2>
        <div class="bg-white p-4 rounded shadow">
          <ul class="space-y-2">
            <li v-for="status in coverStatuses" :key="status.id" class="border-b pb-2">
              <div class="font-medium">{{ status.title }}</div>
              <div class="text-sm text-gray-500">{{ status.description }}</div>
            </li>
          </ul>
        </div>
      </div>

      <div>
        <h2 class="text-xl font-semibold mb-4">Suppliers</h2>
        <div class="bg-white p-4 rounded shadow">
          <ul class="space-y-4">
            <li v-for="supplier in suppliers" :key="supplier.id" class="border-b pb-4">
              <div class="font-medium">{{ supplier.name }}</div>
              <div class="text-sm text-gray-500">{{ supplier.email }}</div>
              <div class="text-sm text-gray-500">{{ supplier.phone }}</div>
              <div class="text-sm text-gray-500">
                {{ supplier.address }}, {{ supplier.city }}, {{ supplier.state }} {{ supplier.zipcode }}
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>
