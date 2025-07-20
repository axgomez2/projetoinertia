<template>
  <teleport to="body">
    <!-- Overlay -->
    <transition name="fade">
      <div v-if="isOpen" @click="$emit('close')" class="fixed inset-0 bg-black bg-opacity-60 z-50"></div>
    </transition>

    <!-- Drawer Panel -->
    <transition name="slide">
      <div v-if="isOpen" class="fixed top-0 left-0 h-full w-80 bg-zinc-800 text-white shadow-xl z-50 flex flex-col">
        <!-- Header -->
        <div class="p-4 border-b border-slate-700">
          <div v-if="user" class="flex items-center gap-3">
            <div class="flex items-center justify-center w-12 h-12 bg-yellow-400 text-black rounded-full font-bold text-xl">
              {{ userInitials }}
            </div>
            <div>
              <p class="font-bold text-lg">{{ user.name }}</p>
              <p class="text-sm text-gray-400">{{ user.email }}</p>
            </div>
          </div>
          <div v-else>
            <h2 class="text-xl font-bold">Menu</h2>
          </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
          <!-- User Logged In Links -->
          <div v-if="user">
            <Link href="#" @click="$emit('close')" class="block py-2 px-3 rounded-md hover:bg-slate-700 transition-colors">Meu Perfil</Link>
            <Link href="#" @click="$emit('close')" class="block py-2 px-3 rounded-md hover:bg-slate-700 transition-colors">Meus Pedidos</Link>
            <Link href="#" @click="$emit('close')" class="block py-2 px-3 rounded-md hover:bg-slate-700 transition-colors">Wantlist</Link>
            <Link href="#" @click="$emit('close')" class="block py-2 px-3 rounded-md hover:bg-slate-700 transition-colors">Wishlist</Link>
          </div>
          
          <!-- Separator -->
          <div class="border-t border-slate-700 my-4"></div>

          <!-- General Links -->
          <Link href="#" @click="$emit('close')" class="block py-2 px-3 rounded-md hover:bg-slate-700 transition-colors">Início</Link>
          <a href="#" @click="$emit('close')" class="block py-2 px-3 rounded-md hover:bg-slate-700 transition-colors">Todos os Discos</a>
          <a href="#" @click="$emit('close')" class="block py-2 px-3 rounded-md hover:bg-slate-700 transition-colors">Notícias</a>
          <a href="#" @click="$emit('close')" class="block py-2 px-3 rounded-md hover:bg-slate-700 transition-colors">Sobre</a>
        </nav>

        <!-- Footer / Auth Actions -->
        <div class="p-4 border-t border-slate-700 mt-auto">
          <div v-if="user">
            <button @click="$emit('logout')" class="w-full text-left py-2 px-3 rounded-md bg-red-600 hover:bg-red-700 transition-colors font-semibold flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
              <span>Logout</span>
            </button>
          </div>
          <div v-else class="space-y-3">
            <Link :href="route('login')" @click="$emit('close')" class="block w-full text-center py-2 px-3 rounded-md bg-yellow-400 text-black hover:bg-yellow-500 transition-colors font-semibold">
              Entrar
            </Link>
            <Link :href="route('register')" @click="$emit('close')" class="block w-full text-center py-2 px-3 rounded-md bg-slate-700 hover:bg-slate-600 transition-colors font-semibold">
              Registrar
            </Link>
          </div>
        </div>
      </div>
    </transition>
  </teleport>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  isOpen: Boolean,
  user: Object,
  userInitials: String,
  categories: Array, // Placeholder for future use
});

defineEmits(['close', 'logout']);
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

.slide-enter-active, .slide-leave-active {
  transition: transform 0.3s ease;
}
.slide-enter-from, .slide-leave-to {
  transform: translateX(-100%);
}
</style>
