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

interface Category {
    id: number;
    name: string;
    slug?: string;
}

const props = defineProps<{
    categories: Category[];
}>();

// Estados para o modal
const showModal = ref(false);
const editingCategory = ref<Category | null>(null);

// Formulário reativo
const form = reactive<{
    name: string;
    errors: Record<string, string>;
}>({
    name: '',
    errors: {}
});

// Função para abrir modal de criação
const openCreateModal = () => {
    editingCategory.value = null;
    form.name = '';
    form.errors = {};
    showModal.value = true;
};

// Função para abrir modal de edição
const openEditModal = (category: Category) => {
    editingCategory.value = category;
    form.name = category.name;
    form.errors = {};
    showModal.value = true;
};

// Função para salvar categoria
const saveCategory = () => {
    const url = editingCategory.value
        ? route('admin.settings.categories.update', editingCategory.value.id)
        : route('admin.settings.categories.store');

    const method = editingCategory.value ? 'put' : 'post';

    router[method](url, {
        name: form.name,
        // The slug will be generated on the server
    }, {
        onSuccess: () => {
            showModal.value = false;
            form.name = '';
            form.errors = {};
        },
        onError: (errors) => {
            form.errors = errors;
        }
    });
};

// Função para excluir categoria
const deleteCategory = (category: Category) => {
    if (confirm(`Tem certeza que deseja excluir a categoria "${category.name}"?`)) {
        router.delete(route('admin.settings.categories.destroy', category.id));
    }
};
</script>

<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl text-gray-700 font-bold">Categorias de Discos</h1>
            <Button @click="openCreateModal" class="flex items-center gap-2">
                <Plus class="w-4 h-4" />
                Nova Categoria
            </Button>
        </div>

        <!-- Lista de Categorias -->
        <div class="grid gap-4">
            <Card v-for="category in props.categories" :key="category.id">
                <CardHeader>
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-semibold">{{ category.name }}</h3>
                            <p v-if="category.slug" class="text-gray-600 text-sm mt-1">
                                {{ category.slug }}
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <Button variant="outline" size="sm" @click="openEditModal(category)"
                                class="flex items-center gap-1">
                                <Pencil class="w-3 h-3" />
                                Editar
                            </Button>
                            <Button variant="destructive" size="sm" @click="deleteCategory(category)"
                                class="flex items-center gap-1">
                                <Trash2 class="w-3 h-3" />
                                Excluir
                            </Button>
                        </div>
                    </div>
                </CardHeader>
            </Card>

            <div v-if="!props.categories || props.categories.length === 0" class="text-center py-8 text-gray-500">
                Nenhuma categoria cadastrada. Clique em "Nova Categoria" para começar.
            </div>
        </div>

        <!-- Modal de Criação/Edição -->
        <Dialog v-model:open="showModal">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>
                        {{ editingCategory ? 'Editar Categoria' : 'Nova Categoria' }}
                    </DialogTitle>
                </DialogHeader>

                <form @submit.prevent="saveCategory" class="space-y-4">
                    <div>
                        <Label for="name">Nome da Categoria</Label>
                        <Input id="name" v-model="form.name" placeholder="Ex: Rock, Jazz, Clássico..."
                            :class="{ 'border-red-500': form.errors.name }" />
                        <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                            {{ form.errors.name }}
                        </p>
                    </div>



                    <div class="flex justify-end gap-2 pt-4">
                        <Button type="button" variant="outline" @click="showModal = false">
                            Cancelar
                        </Button>
                        <Button type="submit">
                            {{ editingCategory ? 'Atualizar' : 'Criar' }}
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
