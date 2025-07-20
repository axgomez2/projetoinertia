<template>
  <div>
    <h1 class="mb-8 text-3xl font-bold text-gray-800">Adicionar Novo Disco</h1>

    <!-- ETAPA 1: Busca no Discogs -->
    <div v-if="currentStep === 1" class="bg-white rounded-md shadow p-8">
      <h2 class="text-xl font-bold mb-4 text-gray-800">Passo 1: Buscar no Discogs</h2>
      <form @submit.prevent="searchDiscogs">
        <div class="flex items-center">
          <input v-model="searchQuery" type="text" placeholder="Digite o nome do artista ou do álbum..." class="flex-grow px-4 py-2 text-gray-800 border rounded-l-md focus:ring-indigo-500 focus:border-indigo-500" />
          <button type="submit" :disabled="isLoading" class="px-6 py-2 bg-gray-800 text-white font-semibold rounded-r-md hover:bg-gray-700 disabled:opacity-50">
            <span v-if="isLoading">Buscando...</span>
            <span v-else>Buscar</span>
          </button>
        </div>
      </form>

      <div v-if="searchResults.length > 0" class="mt-6">
        <h3 class="font-bold text-gray-800">Resultados da Busca:</h3>
        <ul class="mt-4 border rounded-md divide-y">
          <li v-for="result in searchResults" :key="result.id" @click="() => selectResult(result.id)" class="p-4 flex items-center hover:bg-gray-100 cursor-pointer">
            <img :src="result.thumb || '/images/placeholder.png'" alt="Capa" class="w-12 h-12 object-cover rounded mr-4">
            <div>
              <p class="font-semibold text-gray-800">{{ result.title }}</p>
              <p class="text-sm text-gray-600">{{ result.year }} - {{ result.country }}</p>
            </div>
          </li>
        </ul>
      </div>
    </div>

    <!-- ETAPA 2: Detalhes e Confirmação -->
    <div v-if="currentStep === 2 && vinylDetails" class="bg-white rounded-md shadow p-8">
      <div class="flex justify-between items-start">
        <h2 class="text-xl font-bold mb-4 text-gray-800">Passo 2: Confirmar Dados do Disco</h2>
        <button @click="resetForm" class="text-sm text-blue-600 hover:underline">Voltar para a busca</button>
      </div>
      <form @submit.prevent="submitVinyl">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Coluna 1: Imagem e Detalhes Principais -->
          <div class="md:col-span-1">
            <h3 class="font-bold mb-2 text-gray-800">Capa do Disco</h3>
            <img :src="vinylDetails.cover_image || '/images/placeholder.png'" class="w-full rounded-lg shadow-md mb-2">
            <button type="button" @click="openImageSelector(vinylDetails, 'vinyl')" class="w-full text-sm py-2 px-4 bg-gray-600 text-white rounded-md hover:bg-gray-700">Trocar Imagem da Capa</button>
          </div>

          <!-- Coluna 2 e 3: Campos do Formulário -->
          <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
              <label class="form-label text-gray-700">Título</label>
              <input v-model="vinylDetails.title" type="text" class="form-input w-full text-gray-700" />
            </div>
            <div>
              <label class="form-label text-gray-700">Ano</label>
              <input v-model="vinylDetails.year" type="number" class="form-input w-full text-gray-700" />
            </div>
            <div>
              <label class="form-label text-gray-700">País</label>
              <input v-model="vinylDetails.country" type="text" class="form-input w-full text-gray-700" />
            </div>
            <div>
              <label class="form-label text-gray-700">Discogs ID</label>
              <input v-model="vinylDetails.discogs_id" type="text" readonly class="form-input w-full bg-gray-100 text-white bg-red-600" />
            </div>
            <div class="sm:col-span-2">
              <label class="form-label text-gray-700">Descrição</label>
              <textarea v-model="vinylDetails.description" rows="4" class="form-input w-full text-gray-700"></textarea>
            </div>
          </div>
        </div>

        <!-- Artistas -->
        <div class="mt-8">
          <h3 class="font-bold mb-4 text-gray-800">Artistas</h3>
          <div class="space-y-4">
            <div v-for="(artist, index) in vinylDetails.artists" :key="artist.id" class="flex items-center gap-4 bg-gray-50 p-3 rounded-md">
              <img :src="artist.image || '/images/placeholder.png'" class="w-16 h-16 object-cover rounded-md shadow-sm">
              <div class="flex-grow">
                <input v-model="artist.name" type="text" class="form-input w-full text-gray-700" />
              </div>
              <button type="button" @click="openImageSelector(artist, 'artist')" class="text-sm py-2 px-4 bg-gray-600 text-white rounded-md hover:bg-gray-700">Trocar Imagem</button>
            </div>
          </div>
        </div>

        <!-- Gravadoras -->
        <div class="mt-8">
          <h3 class="font-bold mb-4 text-gray-800">Gravadoras</h3>
          <div class="space-y-4">
            <div v-for="(label, index) in vinylDetails.record_labels" :key="label.id" class="flex items-center gap-4 bg-gray-50 p-3 rounded-md">
              <img :src="label.logo || '/images/placeholder.png'" class="w-16 h-16 object-cover rounded-md shadow-sm">
              <div class="flex-grow">
                <input v-model="label.name" type="text" class="form-input w-full text-gray-700" />
              </div>
              <button type="button" @click="openImageSelector(label, 'label')" class="text-sm py-2 px-4 bg-gray-600 text-white rounded-md hover:bg-gray-700">Trocar Logo</button>
            </div>
          </div>
        </div>
        
        <!-- Faixas -->
        <div class="mt-8">
            <h3 class="font-bold mb-4 text-gray-800">Faixas</h3>
            <div class="space-y-2">
                <div v-for="(track, index) in vinylDetails.tracks" :key="index" class="grid grid-cols-12 gap-2 items-center">
                    <span class="col-span-1 text-right text-gray-500 font-mono">{{ track.position }}</span>
                    <div class="col-span-8">
                        <input v-model="track.name" type="text" class="form-input w-full text-sm text-gray-700" />
                    </div>
                    <div class="col-span-3">
                        <input v-model="track.duration" type="text" class="form-input w-full text-sm text-gray-700" />
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-end">
          <button type="submit" :disabled="isLoading" class="px-6 py-3 bg-indigo-600 text-white font-bold rounded-md hover:bg-indigo-500 disabled:opacity-50">
            <span v-if="isLoading">Salvando...</span>
            <span v-else>Salvar Disco</span>
          </button>
        </div>
      </form>
    </div>

    <!-- Modal de Seleção de Imagem -->
    <div v-if="showImageModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50 p-4">
      <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-full overflow-y-auto">
        <div class="p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Selecione uma Imagem</h3>
            <button @click="showImageModal = false" class="text-gray-500 hover:text-gray-800">&times;</button>
          </div>
          <div v-if="modalImages && modalImages.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            <div v-for="(image, index) in modalImages" :key="index" @click="selectImage(image)" class="cursor-pointer border-2 border-transparent hover:border-indigo-500 rounded-md overflow-hidden">
              <img :src="image.uri" class="w-full h-full object-cover">
            </div>
          </div>
          <div v-else>
            <p class="text-gray-600">Nenhuma imagem adicional disponível.</p>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';

const currentStep = ref(1);
const searchQuery = ref('');
const searchResults = ref([]);
const isLoading = ref(false);
const vinylDetails = ref(null);

// Modal state
const showImageModal = ref(false);
const modalImages = ref([]);
const currentItem = ref(null); // Guarda o objeto (vinyl, artist, ou label) sendo editado
const currentItemType = ref(''); // 'vinyl', 'artist', ou 'label'

const searchDiscogs = async () => {
  if (!searchQuery.value) return;
  isLoading.value = true;
  try {
    const response = await axios.get(route('admin.vinyls.searchDiscogs', { query: searchQuery.value }));
    searchResults.value = response.data;
  } catch (error) {
    console.error('Erro ao buscar no Discogs:', error);
    alert('Não foi possível realizar a busca. Tente novamente.');
  } finally {
    isLoading.value = false;
  }
};

const selectResult = async (discogsId) => {
  isLoading.value = true;
  try {
    const response = await axios.get(route('admin.vinyls.getDiscogsDetails', { discogs_id: discogsId }));
    const details = response.data;

    // Prepara os dados para o formulário, definindo a imagem/logo padrão
    details.artists = details.artists.map(artist => ({
      ...artist,
      image: artist.images && artist.images.length > 0 ? artist.images[0].uri : null
    }));

    details.record_labels = details.record_labels.map(label => ({
      ...label,
      logo: label.images && label.images.length > 0 ? label.images[0].uri : null
    }));

    vinylDetails.value = details;
    currentStep.value = 2;

  } catch (error) {
    console.error('Erro ao obter detalhes do disco:', error);
    alert('Não foi possível carregar os detalhes do disco. Tente novamente.');
  } finally {
    isLoading.value = false;
  }
};

const form = useForm({
  title: '',
  year: null,
  country: '',
  discogs_id: '',
  description: '',
  discogs_url: '',
  cover_image: '',
  artists: [],
  record_labels: [],
  tracks: [],
});

const submitVinyl = () => {
  if (!vinylDetails.value) return;

  // Preenche o formulário do Inertia com os dados atuais
  form.title = vinylDetails.value.title;
  form.year = vinylDetails.value.year;
  form.country = vinylDetails.value.country;
  form.discogs_id = String(vinylDetails.value.discogs_id);
  form.description = vinylDetails.value.description;
  form.discogs_url = vinylDetails.value.discogs_url;
  form.cover_image = vinylDetails.value.cover_image;
  form.artists = vinylDetails.value.artists.map(a => ({ id: a.id, name: a.name, image: a.image, discogs_url: a.discogs_url, profile: a.profile }));
  form.record_labels = vinylDetails.value.record_labels.map(l => ({ id: l.id, name: l.name, logo: l.logo, discogs_url: l.discogs_url, profile: l.profile }));
  form.tracks = vinylDetails.value.tracks;

  form.post(route('admin.vinyls.store'), {
    onStart: () => isLoading.value = true,
    onFinish: () => isLoading.value = false,
    onError: (errors) => {
      console.error('Erro ao salvar o disco:', errors);
      alert('Ocorreu um erro inesperado ao salvar o disco. Por favor, tente novamente.');
    },
    onSuccess: () => {
      alert('Disco salvo com sucesso!');
      resetForm();
    }
  });
};

const resetForm = () => {
  currentStep.value = 1;
  searchQuery.value = '';
  searchResults.value = [];
  vinylDetails.value = null;
  form.reset();
};

const openImageSelector = (item, type) => {
  modalImages.value = item.images || [];
  currentItem.value = item;
  currentItemType.value = type;
  showImageModal.value = true;
};

const selectImage = (image) => {
  if (!currentItem.value) return;

  const imageUrl = image.uri;

  switch (currentItemType.value) {
    case 'vinyl':
      currentItem.value.cover_image = imageUrl;
      break;
    case 'artist':
      currentItem.value.image = imageUrl;
      break;
    case 'label':
      currentItem.value.logo = imageUrl;
      break;
  }

  showImageModal.value = false;
};


defineOptions({
  layout: AdminLayout,
});
</script>

<style scoped>
.form-label {
  @apply block text-sm font-medium text-gray-700 mb-1;
}
.form-input {
  @apply shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md;
}
</style>
