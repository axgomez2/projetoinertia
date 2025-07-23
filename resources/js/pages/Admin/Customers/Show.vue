<template>
  <AdminLayout :title="`Cliente: ${customer.name}`">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex justify-between items-center">
          <div>
            <h1 class="text-2xl font-semibold text-gray-900">{{ customer.name }}</h1>
            <p class="mt-1 text-sm text-gray-600">
              {{ customer.email }}
            </p>
          </div>
          <div>
            <Link :href="route('admin.customers.index')" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
              Voltar para Lista
            </Link>
          </div>
        </div>

        <!-- Customer Overview -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
          <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <div>
              <h3 class="text-lg leading-6 font-medium text-gray-900">Informações do Cliente</h3>
              <p class="mt-1 max-w-2xl text-sm text-gray-500">Detalhes pessoais e informações de conta.</p>
            </div>
            <div>
              <button @click="openStatusModal = true" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm" :class="statusButtonClass">
                {{ statusLabel }}
              </button>
            </div>
          </div>
          <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
            <dl class="sm:divide-y sm:divide-gray-200">
              <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Nome completo</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ customer.name }}</dd>
              </div>
              <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Email</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ customer.email }}</dd>
              </div>
              <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Data de registro</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ formatDate(customer.created_at) }}</dd>
              </div>
              <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Status de verificação</dt>
                <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="customer.email_verified_at ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">
                    {{ customer.email_verified_at ? 'Verificado' : 'Não Verificado' }}
                  </span>
                </dd>
              </div>
              <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Último login</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ customer.last_login_at ? formatDate(customer.last_login_at) : 'Nunca' }}</dd>
              </div>
            </dl>
          </div>
        </div>

        <!-- Customer Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                  <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Total de Pedidos</dt>
                    <dd class="text-2xl font-semibold text-gray-900">{{ statistics.total_orders }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                  <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Gasto</dt>
                    <dd class="text-2xl font-semibold text-gray-900">R$ {{ formatCurrency(statistics.total_spent) }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                  <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Valor Médio do Pedido</dt>
                    <dd class="text-2xl font-semibold text-gray-900">R$ {{ formatCurrency(statistics.average_order_value) }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                  <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Último Pedido</dt>
                    <dd class="text-lg font-semibold text-gray-900">{{ statistics.last_order_date ? formatDate(statistics.last_order_date) : 'N/A' }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tabs -->
        <div class="mb-6">
          <div class="border-b border-gray-200">
            <nav class="-mb-px flex" aria-label="Tabs">
              <button @click="activeTab = 'orders'" class="py-4 px-1 border-b-2 font-medium text-sm" :class="activeTab === 'orders' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" aria-current="page">
                Pedidos ({{ customer.orders.length }})
              </button>
              <button @click="activeTab = 'addresses'" class="ml-8 py-4 px-1 border-b-2 font-medium text-sm" :class="activeTab === 'addresses' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                Endereços ({{ customer.addresses.length }})
              </button>
              <button @click="activeTab = 'wishlist'" class="ml-8 py-4 px-1 border-b-2 font-medium text-sm" :class="activeTab === 'wishlist' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                Lista de Desejos ({{ customer.wishlists.length }})
              </button>
              <button @click="activeTab = 'wantlist'" class="ml-8 py-4 px-1 border-b-2 font-medium text-sm" :class="activeTab === 'wantlist' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                Lista de Procurados ({{ customer.wantlists.length }})
              </button>
              <button @click="activeTab = 'cart'" class="ml-8 py-4 px-1 border-b-2 font-medium text-sm" :class="activeTab === 'cart' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                Carrinho ({{ customer.cart_items.length }})
              </button>
              <button @click="activeTab = 'notes'" class="ml-8 py-4 px-1 border-b-2 font-medium text-sm" :class="activeTab === 'notes' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                Notas ({{ notes.length }})
              </button>
              <button @click="activeTab = 'communications'" class="ml-8 py-4 px-1 border-b-2 font-medium text-sm" :class="activeTab === 'communications' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                Comunicações ({{ communications.length }})
              </button>
            </nav>
          </div>
        </div>

        <!-- Tab Content -->
        <div v-if="activeTab === 'orders'" class="bg-white shadow overflow-hidden sm:rounded-md">
          <ul class="divide-y divide-gray-200">
            <li v-for="order in customer.orders" :key="order.id" class="px-4 py-4 sm:px-6">
              <div class="flex items-center justify-between">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStatusClass(order.status)">
                      {{ order.status_label }}
                    </span>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">
                      Pedido #{{ order.order_number }}
                    </div>
                    <div class="text-sm text-gray-500">
                      {{ formatDate(order.created_at) }} · {{ order.items_count }} itens · R$ {{ formatCurrency(order.total_amount) }}
                    </div>
                  </div>
                </div>
                <div>
                  <Link :href="route('admin.orders.online.show', order.id)" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Ver Detalhes
                  </Link>
                </div>
              </div>
              <div class="mt-2">
                <div class="text-sm text-gray-500">
                  <span class="font-medium">Pagamento:</span>
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getPaymentStatusClass(order.payment_status)">
                    {{ order.payment_status_label }}
                  </span>
                </div>
              </div>
              <div class="mt-2">
                <div class="text-sm text-gray-700">
                  <span class="font-medium">Itens:</span>
                  <ul class="mt-1 pl-5 list-disc">
                    <li v-for="item in order.items.slice(0, 3)" :key="item.id">
                      {{ item.product_name }} ({{ item.quantity }}x) - R$ {{ formatCurrency(item.price) }}
                    </li>
                    <li v-if="order.items.length > 3" class="text-gray-500 italic">
                      + {{ order.items.length - 3 }} outros itens
                    </li>
                  </ul>
                </div>
              </div>
            </li>
            <li v-if="customer.orders.length === 0" class="px-4 py-4 sm:px-6 text-center text-gray-500">
              Este cliente ainda não fez nenhum pedido.
            </li>
          </ul>
        </div>

        <div v-if="activeTab === 'addresses'" class="bg-white shadow overflow-hidden sm:rounded-md">
          <ul class="divide-y divide-gray-200">
            <li v-for="address in customer.addresses" :key="address.id" class="px-4 py-4 sm:px-6">
              <div class="flex items-center justify-between">
                <div>
                  <div class="text-sm font-medium text-gray-900">
                    {{ address.type === 'billing' ? 'Endereço de Cobrança' : 'Endereço de Entrega' }}
                    <span v-if="address.is_default" class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                      Padrão
                    </span>
                  </div>
                  <div class="text-sm text-gray-500 mt-1">
                    {{ address.first_name }} {{ address.last_name }}
                  </div>
                  <div class="text-sm text-gray-500">
                    {{ address.address_line_1 }}
                    <span v-if="address.address_line_2">, {{ address.address_line_2 }}</span>
                  </div>
                  <div class="text-sm text-gray-500">
                    {{ address.city }}, {{ address.state }} {{ address.postal_code }}
                  </div>
                  <div class="text-sm text-gray-500">
                    {{ address.country }}
                  </div>
                  <div class="text-sm text-gray-500 mt-1">
                    <span class="font-medium">Telefone:</span> {{ address.phone }}
                  </div>
                </div>
              </div>
            </li>
            <li v-if="customer.addresses.length === 0" class="px-4 py-4 sm:px-6 text-center text-gray-500">
              Este cliente ainda não cadastrou nenhum endereço.
            </li>
          </ul>
        </div>

        <div v-if="activeTab === 'wishlist'" class="bg-white shadow overflow-hidden sm:rounded-md">
          <ul class="divide-y divide-gray-200">
            <li v-for="item in customer.wishlists" :key="item.id" class="px-4 py-4 sm:px-6">
              <div class="flex items-center justify-between">
                <div>
                  <div class="text-sm font-medium text-gray-900">
                    {{ item.product_name }}
                  </div>
                  <div class="text-sm text-gray-500">
                    Adicionado em {{ formatDate(item.created_at) }}
                  </div>
                </div>
                <div>
                  <Link :href="`/admin/vinyls/${item.product_id}`" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Ver Produto
                  </Link>
                </div>
              </div>
            </li>
            <li v-if="customer.wishlists.length === 0" class="px-4 py-4 sm:px-6 text-center text-gray-500">
              Este cliente não tem itens na lista de desejos.
            </li>
          </ul>
        </div>

        <div v-if="activeTab === 'wantlist'" class="bg-white shadow overflow-hidden sm:rounded-md">
          <ul class="divide-y divide-gray-200">
            <li v-for="item in customer.wantlists" :key="item.id" class="px-4 py-4 sm:px-6">
              <div class="flex items-center justify-between">
                <div>
                  <div class="text-sm font-medium text-gray-900">
                    {{ item.product_name }}
                  </div>
                  <div class="text-sm text-gray-500">
                    Adicionado em {{ formatDate(item.created_at) }}
                  </div>
                </div>
                <div>
                  <Link :href="`/admin/vinyls/${item.product_id}`" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Ver Produto
                  </Link>
                </div>
              </div>
            </li>
            <li v-if="customer.wantlists.length === 0" class="px-4 py-4 sm:px-6 text-center text-gray-500">
              Este cliente não tem itens na lista de procurados.
            </li>
          </ul>
        </div>

        <div v-if="activeTab === 'cart'" class="bg-white shadow overflow-hidden sm:rounded-md">
          <ul class="divide-y divide-gray-200">
            <li v-for="item in customer.cart_items" :key="item.id" class="px-4 py-4 sm:px-6">
              <div class="flex items-center justify-between">
                <div>
                  <div class="text-sm font-medium text-gray-900">
                    {{ item.product_name }}
                  </div>
                  <div class="text-sm text-gray-500">
                    {{ item.quantity }}x · R$ {{ formatCurrency(item.price) }} cada · Total: R$ {{ formatCurrency(item.total) }}
                  </div>
                  <div class="text-sm text-gray-500">
                    Adicionado em {{ formatDate(item.created_at) }}
                    <span v-if="item.created_at !== item.updated_at" class="ml-2 text-xs text-gray-400">
                      (Atualizado em {{ formatDate(item.updated_at) }})
                    </span>
                  </div>
                </div>
                <div>
                  <Link :href="`/admin/vinyls/${item.product_id}`" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Ver Produto
                  </Link>
                </div>
              </div>
            </li>
            <li v-if="customer.cart_items.length === 0" class="px-4 py-4 sm:px-6 text-center text-gray-500">
              Este cliente não tem itens no carrinho.
            </li>
          </ul>
        </div>

        <div v-if="activeTab === 'notes'" class="bg-white shadow overflow-hidden sm:rounded-md">
          <div class="p-4">
            <form @submit.prevent="addNote">
              <div>
                <label for="note" class="block text-sm font-medium text-gray-700">Adicionar Nota</label>
                <div class="mt-1">
                  <textarea id="note" v-model="newNote" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Adicione uma nota sobre este cliente..."></textarea>
                </div>
              </div>
              <div class="mt-2 flex justify-end">
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                  Salvar Nota
                </button>
              </div>
            </form>
          </div>
          <ul class="divide-y divide-gray-200">
            <li v-for="note in notes" :key="note.id" class="px-4 py-4 sm:px-6">
              <div class="flex space-x-3">
                <div class="flex-shrink-0">
                  <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                    <span class="text-gray-500 font-medium">{{ note.created_by_name.charAt(0).toUpperCase() }}</span>
                  </div>
                </div>
                <div class="min-w-0 flex-1">
                  <p class="text-sm font-medium text-gray-900">
                    {{ note.created_by_name }}
                  </p>
                  <p class="text-sm text-gray-500">
                    {{ formatDate(note.created_at) }}
                  </p>
                  <div class="mt-2 text-sm text-gray-700">
                    <p>{{ note.note }}</p>
                  </div>
                </div>
              </div>
            </li>
            <li v-if="notes.length === 0" class="px-4 py-4 sm:px-6 text-center text-gray-500">
              Nenhuma nota adicionada para este cliente.
            </li>
          </ul>
        </div>

        <div v-if="activeTab === 'communications'" class="bg-white shadow overflow-hidden sm:rounded-md">
          <div class="p-4">
            <form @submit.prevent="addCommunication">
              <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                <div class="sm:col-span-3">
                  <label for="comm-type" class="block text-sm font-medium text-gray-700">Tipo de Comunicação</label>
                  <select id="comm-type" v-model="newCommunication.type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    <option value="email">Email</option>
                    <option value="phone">Telefone</option>
                    <option value="whatsapp">WhatsApp</option>
                    <option value="other">Outro</option>
                  </select>
                </div>
                <div class="sm:col-span-3">
                  <label for="comm-subject" class="block text-sm font-medium text-gray-700">Assunto</label>
                  <input type="text" id="comm-subject" v-model="newCommunication.subject" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Assunto da comunicação">
                </div>
                <div class="sm:col-span-6">
                  <label for="comm-message" class="block text-sm font-medium text-gray-700">Mensagem</label>
                  <textarea id="comm-message" v-model="newCommunication.message" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Detalhes da comunicação com o cliente..."></textarea>
                </div>
              </div>
              <div class="mt-5 sm:mt-6 sm:flex sm:flex-row-reverse">
                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                  Registrar Comunicação
                </button>
              </div>
            </form>
          </div>
          <ul class="divide-y divide-gray-200">
            <li v-for="comm in communications" :key="comm.id" class="px-4 py-4 sm:px-6">
              <div class="flex space-x-3">
                <div class="flex-shrink-0">
                  <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                    <span class="text-gray-500 font-medium">{{ comm.created_by_name.charAt(0).toUpperCase() }}</span>
                  </div>
                </div>
                <div class="min-w-0 flex-1">
                  <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-900">
                      {{ comm.created_by_name }}
                    </p>
                    <p class="text-sm text-gray-500">
                      {{ formatDate(comm.created_at) }}
                    </p>
                  </div>
                  <div class="mt-1">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getCommunicationTypeClass(comm.type)">
                      {{ getCommunicationTypeLabel(comm.type) }}
                    </span>
                    <span class="ml-2 text-sm font-medium text-gray-900">{{ comm.subject }}</span>
                  </div>
                  <div class="mt-2 text-sm text-gray-700">
                    <p>{{ comm.message }}</p>
                  </div>
                </div>
              </div>
            </li>
            <li v-if="communications.length === 0" class="px-4 py-4 sm:px-6 text-center text-gray-500">
              Nenhuma comunicação registrada para este cliente.
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Status Update Modal -->
    <div v-if="openStatusModal" class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="openStatusModal = false"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
          <div>
            <div class="mt-3 text-center sm:mt-5">
              <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                Atualizar Status do Cliente
              </h3>
              <div class="mt-2">
                <p class="text-sm text-gray-500">
                  Selecione o novo status para este cliente.
                </p>
              </div>
            </div>
          </div>
          <div class="mt-5 sm:mt-6">
            <div class="grid grid-cols-1 gap-3">
              <button @click="updateStatus('active')" class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:text-sm">
                Ativo
              </button>
              <button @click="updateStatus('inactive')" class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:text-sm">
                Inativo
              </button>
              <button @click="updateStatus('blocked')" class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:text-sm">
                Bloqueado
              </button>
              <button @click="openStatusModal = false" class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm">
                Cancelar
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';

const props = defineProps({
  customer: Object,
  statistics: Object,
  notes: Array,
  communications: Array,
});

const activeTab = ref('orders');
const newNote = ref('');
const openStatusModal = ref(false);
const newCommunication = ref({
  type: 'email',
  subject: '',
  message: '',
});

const statusLabel = computed(() => {
  const status = props.customer.status || 'active';
  return {
    'active': 'Ativo',
    'inactive': 'Inativo',
    'blocked': 'Bloqueado',
  }[status] || 'Ativo';
});

const statusButtonClass = computed(() => {
  const status = props.customer.status || 'active';
  return {
    'active': 'bg-green-100 text-green-800',
    'inactive': 'bg-yellow-100 text-yellow-800',
    'blocked': 'bg-red-100 text-red-800',
  }[status] || 'bg-green-100 text-green-800';
});

const addNote = () => {
  if (!newNote.value.trim()) return;

  router.post(route('admin.customers.notes.store', props.customer.id), {
    note: newNote.value,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      newNote.value = '';
    },
  });
};

const addCommunication = () => {
  if (!newCommunication.value.subject.trim() || !newCommunication.value.message.trim()) return;

  router.post(route('admin.customers.communications.store', props.customer.id), {
    type: newCommunication.value.type,
    subject: newCommunication.value.subject,
    message: newCommunication.value.message,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      newCommunication.value = {
        type: 'email',
        subject: '',
        message: '',
      };
    },
  });
};

const updateStatus = (status) => {
  router.patch(route('admin.customers.status.update', props.customer.id), {
    status: status,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      openStatusModal.value = false;
    },
  });
};

const getStatusClass = (status) => {
  return {
    'pending': 'bg-yellow-100 text-yellow-800',
    'processing': 'bg-blue-100 text-blue-800',
    'completed': 'bg-green-100 text-green-800',
    'cancelled': 'bg-red-100 text-red-800',
    'refunded': 'bg-gray-100 text-gray-800',
  }[status] || 'bg-gray-100 text-gray-800';
};

const getPaymentStatusClass = (status) => {
  return {
    'pending': 'bg-yellow-100 text-yellow-800',
    'paid': 'bg-green-100 text-green-800',
    'failed': 'bg-red-100 text-red-800',
    'refunded': 'bg-gray-100 text-gray-800',
  }[status] || 'bg-gray-100 text-gray-800';
};

const getCommunicationTypeClass = (type) => {
  return {
    'email': 'bg-blue-100 text-blue-800',
    'phone': 'bg-green-100 text-green-800',
    'whatsapp': 'bg-green-100 text-green-800',
    'other': 'bg-gray-100 text-gray-800',
  }[type] || 'bg-gray-100 text-gray-800';
};

const getCommunicationTypeLabel = (type) => {
  return {
    'email': 'Email',
    'phone': 'Telefone',
    'whatsapp': 'WhatsApp',
    'other': 'Outro',
  }[type] || 'Outro';
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return new Intl.DateTimeFormat('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }).format(date);
};

const formatCurrency = (value) => {
  if (value === null || value === undefined) return '0,00';
  return parseFloat(value).toLocaleString('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};
</script>
