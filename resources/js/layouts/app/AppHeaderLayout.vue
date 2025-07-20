<template>
  <nav class="bg-zinc-800 border-b border-slate-800 w-full">
    <div class="max-w-7xl mx-auto flex items-center justify-between py-3 px-4 md:py-3 md:px-4">
      <!-- Botão menu mobile -->
      <div class="flex items-center md:hidden">
        <button 
          @click="toggleDrawer"
          class="p-2 text-white hover:text-yellow-300 transition-colors focus:outline-none mr-2">
          <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
        <a href="#" class="flex-shrink-0 flex items-center">
          <!-- <img v-if="logoPath" class="h-10 w-auto m-2" :src="logoPath" :alt="storeName"> -->
          <span class="text-lg font-bold text-white">{{ storeName }}</span>
        </a>
      </div>
      
      <!-- Logo desktop -->
      <div class="items-center space-x-2 min-w-max hidden md:flex">
        <a href="#" class="flex-shrink-0 flex items-center">
          <!-- <img v-if="logoPath" class="sm:h-12 xl:h-16 xl:w-auto sm:w-auto m-2" :src="logoPath" :alt="storeName"> -->
          <span class="text-lg font-bold text-white">{{ storeName }}</span>
        </a>
      </div>
      
      <!-- Campo de busca centralizado (desktop) -->
      <div class="hidden md:flex flex-1 justify-center">
        <form @submit.prevent="() => {}" class="flex w-full max-w-2xl">
          <input 
            type="text" 
            v-model="searchQuery"
            placeholder="Buscar discos, artistas, gêneros..." 
            class="flex-1 pl-4 pr-2 py-3 rounded-l-full border border-zinc-800 bg-zinc-100 text-zinc-900 text-lg font-semibold placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400" 
            style="font-family: 'Montserrat', 'Inter', 'Arial', sans-serif;" />
          <button 
            type="submit" 
            class="bg-yellow-300 text-zinc-900 px-4 rounded-r-full flex items-center justify-center hover:bg-yellow-400 hover:text-black transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z" />
            </svg>
          </button>
        </form>
      </div>
      
      <!-- Ícones mobile (busca, usuário, favoritos, carrinho) -->
      <div class="md:hidden flex items-center space-x-2">
        <!-- Ícone de busca -->
        <div class="relative">
          <button 
            @click="toggleMobileSearch" 
            class="p-2 text-white hover:text-yellow-300 transition-colors focus:outline-none" 
            title="Buscar">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z" />
            </svg>
          </button>
          
          <!-- Overlay e campo de busca centralizado -->
          <div v-if="showMobileSearch" class="fixed inset-0 bg-black bg-opacity-80 z-50 flex items-start justify-center">
            <div class="relative w-full max-w-md mx-auto px-4 mt-[50px]">
              <form @submit.prevent="handleMobileSearch" class="relative">
                <input 
                  type="text" 
                  v-model="mobileSearchQuery"
                  placeholder="Buscar discos, artistas, gêneros..." 
                  class="w-full pl-12 pr-12 py-4 rounded-full border border-slate-700 bg-slate-100 text-stone-900 font-semibold placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 shadow-lg transition-all duration-200 text-xl" 
                  style="font-family: 'Montserrat', 'Inter', 'Arial', sans-serif;" 
                  ref="mobileSearchInput" />
                <button type="submit" class="absolute left-6 top-1/2 transform -translate-y-1/2 text-yellow-400">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z" />
                  </svg>
                </button>
              </form>
              <button 
                @click="toggleMobileSearch" 
                class="absolute right-6 top-1/2 -translate-y-1/2 text-slate-700 hover:text-yellow-400 text-2xl font-bold">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
          </div>
        </div>
        
        <!-- Ícone de usuário/login -->
        <div v-if="user" class="relative">
          <button @click="toggleMobileUserDropdown" class="flex items-center justify-center w-9 h-9 bg-yellow-400 text-black rounded-full font-bold text-sm focus:outline-none hover:bg-yellow-500" title="Minha Conta">
            {{ userInitials }}
          </button>
          <!-- Dropdown do Usuário Mobile -->
          <div v-if="showMobileUserDropdown" class="absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg py-1 z-50 ring-1 ring-black ring-opacity-5">
            <div class="px-4 py-3">
              <p class="text-sm font-semibold text-gray-900">{{ user.name }}</p>
              <p class="text-sm text-gray-500 truncate">{{ user.email }}</p>
            </div>
            <div class="border-t border-gray-100"></div>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Meu Perfil</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Meus Pedidos</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Wantlist</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Wishlist</a>
            <div class="border-t border-gray-100"></div>
            <Link :href="route('logout')" method="post" as="button" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</Link>
          </div>
        </div>
        <div v-else>
          <Link :href="route('login')" class="p-2 text-white hover:text-yellow-300 transition-colors focus:outline-none" title="Entrar">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
            </svg>
          </Link>
          
          <!-- Modal de login mobile -->
          <!-- <div v-if="showMobileLogin" class="fixed inset-0 z-50 overflow-y-auto overflow-x-hidden"> -->
            <!-- Overlay com opacidade -->
            <!-- <div class="fixed inset-0 bg-black opacity-50" @click="toggleMobileLogin"></div> -->
            
            <!-- Container do modal -->
            <!-- <div class="flex min-h-full items-center justify-center p-4 text-center sm:items-center sm:p-0">
              <div class="relative w-full max-w-xs mx-auto bg-white border border-slate-700 rounded-lg shadow-xl overflow-y-auto max-h-[90vh] z-[60]">
                <button @click="toggleMobileLogin" class="absolute top-2 right-2 text-sky-600 hover:text-sky-700">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button> -->
                
                <!-- Conteúdo do modal de login mobile -->
                <!-- <ModalLogin 
                  @close="toggleMobileLogin" 
                  @login="handleLogin"
                  @login-with-google="handleGoogleLogin" /> -->
              <!-- </div>
            </div>
          </div> -->
        </div>
      </div>
      
            <!-- Ações à direita (desktop) -->
      <div class="hidden md:flex items-center space-x-6 min-w-max">
        <!-- Lógica de Autenticação -->
        <div class="relative">
          <!-- Usuário Logado -->
          <div v-if="user">
            <button @click="toggleUserDropdown" class="flex items-center justify-center w-10 h-10 bg-yellow-400 text-black rounded-full font-bold text-md focus:outline-none hover:bg-yellow-500" title="Minha Conta">
              {{ userInitials }}
            </button>
            <!-- Dropdown do Usuário -->
            <div v-if="showUserDropdown" class="absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg py-1 z-50 ring-1 ring-black ring-opacity-5">
              <div class="px-4 py-3">
                <p class="text-sm font-semibold text-gray-900">{{ user.name }}</p>
                <p class="text-sm text-gray-500 truncate">{{ user.email }}</p>
              </div>
              <div class="border-t border-gray-100"></div>
              <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Meu Perfil</a>
              <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Meus Pedidos</a>
              <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Wantlist</a>
              <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Wishlist</a>
              <div class="border-t border-gray-100"></div>
              <Link :href="route('logout')" method="post" as="button" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</Link>
            </div>
          </div>

          <!-- Visitante -->
          <div v-else class="flex items-center space-x-4">
            <Link :href="route('login')" class="flex flex-col items-center text-white hover:text-yellow-300 transition-colors focus:outline-none" title="Entrar">
              <svg class="w-6 h-6 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              <span class="text-xs">Entrar</span>
            </Link>
            <Link :href="route('register')" class="px-4 py-2 rounded-full bg-yellow-300 text-black font-semibold hover:bg-yellow-400 transition-colors text-sm">
              Registrar
            </Link>
          </div>
        </div>

        <!-- Favoritos -->
        <!-- Overlay para dropdowns -->
        <div v-if="showUserDropdown" class="fixed inset-0 z-40" @click="closeUserDropdown"></div>
        <div>
          <!-- Usuário não logado: Exibir toast ao clicar -->
          <button @click="showWishlistToast" class="flex flex-col items-center text-white hover:text-yellow-300 transition-colors" title="Favoritos">
            <svg class="w-6 h-6 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
            <span class="text-xs">Favoritos</span>
          </button>
        </div>
        
        <!-- Carrinho (com dropdown) -->
        <div class="relative">
          <a href="#" class="flex flex-col items-center text-white hover:text-yellow-300 transition-colors focus:outline-none" title="Carrinho">
            <svg class="w-6 h-6 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span class="text-xs">Carrinho</span>
            <!-- <span v-if="cartCount > 0" class="absolute -top-1 -right-1 bg-yellow-400 text-black text-xs rounded-full px-1.5 py-0.5 font-bold">
              {{ cartCount }}
            </span> -->
          </a>
        </div>
      </div>
    </div>
    
    <!-- Separador e menu inferior (desktop) -->
    <div class="hidden md:block max-w-7xl mx-auto">
      <div class="border-t border-slate-800 opacity-60"></div>
    </div>
    
    <div class="max-w-7xl mx-auto hidden md:flex items-center gap-2 py-2 px-4 justify-center relative">
      <button 
        @click="toggleMegaMenu" 
        type="button" 
        class="flex items-center bg-white px-4 py-2 rounded-full text-black font-medium hover:bg-yellow-300 hover:text-black transition-colors focus:outline-none" 
        aria-expanded="false" 
        aria-haspopup="true">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
        Discos de Vinil
        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </button>
      <a href="#" class="text-white font-semibold text-md ml-4 hover:text-yellow-300 transition-colors">Inicio</a>
      <a href="#" class="text-white hover:text-yellow-300 transition-colors text-md font-medium ml-4">Todos os discos</a>
      <a href="#" class="text-white hover:text-yellow-300 transition-colors text-md font-medium ml-4">Vinyl para DJs</a>
      <a href="#" class="text-white hover:text-yellow-300 transition-colors text-md font-medium ml-4">Area de Membros</a>
      <a href="#" class="text-white hover:text-yellow-300 transition-colors text-md font-medium ml-4">Playlists</a>
      <a href="#" class="text-white hover:text-yellow-300 transition-colors text-md font-medium ml-4">Noticias</a>
      <a href="#" class="text-white hover:text-yellow-300 transition-colors text-md font-medium ml-4">Sobre</a>
      <span class="mx-2 text-slate-700">|</span>
      <a href="#" class=" px-4 py-2 rounded-full text-yellow-300 hover:bg-yellow-300 hover:text-black transition-colors focus:outline-none font-semibold text-md ml-4">OFERTAS</a>
      
      <!-- Mega menu -->
      <Megamenu 
        :isOpen="showMegaMenu"
        :categories="[]"
        @close="closeMegaMenu" />
    </div>

    <!-- Drawer Menu Mobile -->
    <!-- Flash Message -->
    <div v-if="flashMessage" class="fixed top-5 right-5 z-50 p-4 rounded-md text-white" :class="flashType === 'success' ? 'bg-green-500' : 'bg-red-500'">
      {{ flashMessage }}
    </div>

    <Drawer 
      :isOpen="showDrawer"
      :categories="[]" 
      :user="user"
      :userInitials="userInitials"
      @close="closeDrawer"
      @logout="handleLogout" />
  </nav>

  <!-- Page Content -->
  <main>
    <slot />
  </main>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue';
import { router, usePage, Link } from '@inertiajs/vue3';
import Megamenu from './Megamenu.vue';
import Drawer from './Drawer.vue';

// Obter props e usuário da página
const page = usePage();
const user = computed(() => page.props.auth.user);
const props = defineProps({
  store: {
    type: Object,
    default: () => ({}),
  },
});

// Estado do flash message
const flashMessage = ref('');
const flashType = ref('');
watch(() => page.props.flash, (flash) => {
  if (flash && flash.success) {
    flashMessage.value = flash.success;
    flashType.value = 'success';
    setTimeout(() => flashMessage.value = '', 3000);
  }
  if (flash && flash.error) {
    flashMessage.value = flash.error;
    flashType.value = 'error';
    setTimeout(() => flashMessage.value = '', 3000);
  }
}, { deep: true });

// Estado local da UI
const searchQuery = ref('');
const mobileSearchQuery = ref('');
const showMobileSearch = ref(false);
const showUserDropdown = ref(false);
const showMobileUserDropdown = ref(false);
const showMegaMenu = ref(false);
const showDrawer = ref(false);
const mobileSearchInput = ref(null);

// Propriedades computadas
const storeName = computed(() => props.store?.name || 'RDV Discos');
const logoPath = computed(() => props.store?.logo_path ? `/storage/${props.store.logo_path}` : null);
const userInitials = computed(() => {
  if (user.value && user.value.name) {
    const names = user.value.name.split(' ').filter(n => n);
    if (names.length > 1) {
      return `${names[0][0]}${names[names.length - 1][0]}`.toUpperCase();
    }
    return names[0].substring(0, 2).toUpperCase();
  }
  return '';
});

// --- Métodos ---

const handleSearch = () => {
  alert('Funcionalidade de busca ainda não implementada.');
};

const handleMobileSearch = () => {
  alert('Funcionalidade de busca ainda não implementada.');
  showMobileSearch.value = false;
};

const toggleUserDropdown = () => {
  showUserDropdown.value = !showUserDropdown.value;
};

const closeUserDropdown = () => {
  showUserDropdown.value = false;
};

const toggleMobileUserDropdown = () => {
  showMobileUserDropdown.value = !showMobileUserDropdown.value;
};

const closeMobileUserDropdown = () => {
  showMobileUserDropdown.value = false;
};

const handleLogout = () => {
  router.post(route('logout'));
};

const toggleMobileSearch = async () => {
  showMobileSearch.value = !showMobileSearch.value;
  if (showMobileSearch.value) {
    await nextTick();
    mobileSearchInput.value?.focus();
  }
};

const toggleMegaMenu = () => {
  showMegaMenu.value = !showMegaMenu.value;
};

const closeMegaMenu = () => {
  showMegaMenu.value = false;
};

const toggleDrawer = () => {
  showDrawer.value = !showDrawer.value;
};

const closeDrawer = () => {
  showDrawer.value = false;
};

const showWishlistToast = () => {
  alert('Faça login para acessar sua lista de desejos');
};

// --- Ciclo de Vida e Eventos Globais ---

const handleEscapeKey = (event) => {
  if (event.key === 'Escape') {
    if (showUserDropdown.value) closeUserDropdown();
    if (showMobileUserDropdown.value) closeMobileUserDropdown();
    if (showMegaMenu.value) closeMegaMenu();
    if (showDrawer.value) closeDrawer();
    if (showMobileSearch.value) showMobileSearch.value = false;
  }
};

onMounted(() => {
  document.addEventListener('keydown', handleEscapeKey);
});

onUnmounted(() => {
  document.removeEventListener('keydown', handleEscapeKey);
});
</script>

<style scoped>
/* Estilos específicos para o navbar, se necessário */
</style> 
