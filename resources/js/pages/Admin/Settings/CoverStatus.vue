<script setup lang="ts">
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardHeader } from '@/components/ui/card';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Pencil, Trash2, Plus } from 'lucide-vue-next';

defineOptions({
  layout: AdminLayout,
});

interface CoverStatus {
  id: number;
  title: string;
  description?: string;
}

const props = defineProps<{
  coverStatuses: CoverStatus[];
}>();

// Estados para o modal
const showModal = ref(false);
const editingStatus = ref<CoverStatus | null>(null);

// Formulário reativo
const form = reactive<{
  name: string;
  description: string;
  errors: Record<string, string>;
}>({
  name: '',
  description: '',
  errors: {}
});

// Função para abrir modal de criação
const openCreateModal = () => {
  editingStatus.value = null;
  form.name = '';
  form.description = '';
  form.errors = {};
  showModal.value = true;
};

// Função para abrir modal de edição
const openEditModal = (status: CoverStatus) => {
  editingStatus.value = status;
  form.name = status.title;
  form.description = status.description || '';
  form.errors = {};
  showModal.value = true;
};

// Função para salvar status
const saveStatus = () => {
  const url = editingStatus.value
    ? route('admin.settings.cover-status.update', editingStatus.value.id)
    : route('admin.settings.cover-status.store');

  const method = editingStatus.value ? 'put' : 'post';

  router[method](url, {
    title: form.name, // Using name field for title
    description: form.description,
  }, {
    onSuccess: () => {
      showModal.value = false;
      form.name = '';
      form.description = '';
      form.errors = {};
    },
    onError: (errors) => {
      form.errors = errors;
    }
  });
};

// Função para excluir status
const deleteStatus = (status: CoverStatus) => {
  if (confirm(`Tem certeza que deseja excluir o status "${status.title}"?`)) {
    router.delete(route('admin.settings.cover-status.destroy', status.id));
  }
};
</script>

<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl text-gray-700 font-bold">Status da Capa</h1>
      <Button @click="openCreateModal" class="flex items-center gap-2">
        <Plus class="w-4 h-4" />
        Novo Status
      </Button>
    </div>

    <!-- Lista de Status -->
    <div class="grid gap-4">
      <Card v-for="status in props.coverStatuses" :key="status.id">
        <CardHeader>
          <div class="flex justify-between items-start">
            <div>
              <h3 class="text-lg font-semibold">{{ status.title }}</h3>
              <p v-if="status.description" class="text-gray-600 text-sm mt-1">
                {{ status.description }}
              </p>
            </div>
            <div class="flex gap-2">
              <Button
                variant="outline"
                size="sm"
                @click="openEditModal(status)"
                class="flex items-center gap-1"
              >
                <Pencil class="w-3 h-3" />
                Editar
              </Button>
              <Button
                variant="destructive"
                size="sm"
                @click="deleteStatus(status)"
                class="flex items-center gap-1"
              >
                <Trash2 class="w-3 h-3" />
                Excluir
              </Button>
            </div>
          </div>
        </CardHeader>
      </Card>

      <div v-if="!props.coverStatuses || props.coverStatuses.length === 0" class="text-center py-8 text-gray-500">
        Nenhum status de capa cadastrado. Clique em "Novo Status" para começar.
      </div>
    </div>

    <!-- Modal de Criação/Edição -->
    <Dialog v-model:open="showModal">
      <DialogContent class="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>
            {{ editingStatus ? 'Editar Status da Capa' : 'Novo Status da Capa' }}
          </DialogTitle>
        </DialogHeader>

        <form @submit.prevent="saveStatus" class="space-y-4">
          <div>
            <Label for="name">Nome do Status</Label>
            <Input
              id="name"
              v-model="form.name"
              placeholder="Ex: Perfeita, Boa, Danificada..."
              :class="{ 'border-red-500': form.errors.name }"
            />
            <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">
              {{ form.errors.name }}
            </p>
          </div>

          <div>
            <Label for="description">Descrição (opcional)</Label>
            <Input
              id="description"
              v-model="form.description"
              placeholder="Descrição do status da capa..."
              :class="{ 'border-red-500': form.errors.description }"
            />
            <p v-if="form.errors.description" class="text-red-500 text-sm mt-1">
              {{ form.errors.description }}
            </p>
          </div>

          <div class="flex justify-end gap-2 pt-4">
            <Button
              type="button"
              variant="outline"
              @click="showModal = false"
            >
              Cancelar
            </Button>
            <Button type="submit">
              {{ editingStatus ? 'Atualizar' : 'Criar' }}
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  </div>
</template>
