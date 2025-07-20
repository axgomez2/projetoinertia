<template>
  <header class="bg-white shadow-sm p-4 flex justify-end text-gray-800 items-center">
    <div v-if="$page.props.auth.user">
      <span>{{ $page.props.auth.user.name }}</span>
    </div>
  </header>
</template>

<script setup>
// Lógica do Topnav, como dropdown de usuário, pode ser adicionada aqui.
</script>
