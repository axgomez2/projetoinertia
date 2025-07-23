
<script setup lang="ts">
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardHeader } from '@/components/ui/card';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Pencil, Trash2, Plus, Mail, Phone, MapPin } from 'lucide-vue-next';

defineOptions({
    layout: AdminLayout,
});

interface Supplier {
    id: number;
    name: string;
    email?: string;
    phone?: string;
    notes?: string;
}

const props = defineProps<{
    suppliers: Supplier[];
}>();

// Estados para o modal
const showModal = ref(false);
const editingSupplier = ref<Supplier | null>(null);

// Formulário reativo
const form = reactive<{
    name: string;
    email: string;
    phone: string;
    notes: string;
    errors: Record<string, string>;
}>({
    name: '',
    email: '',
    phone: '',
    notes: '',
    errors: {}
});

// Função para abrir modal de criação
const openCreateModal = () => {
    editingSupplier.value = null;
    resetForm();
    showModal.value = true;
};

// Função para abrir modal de edição
const openEditModal = (supplier: Supplier) => {
    editingSupplier.value = supplier;
    form.name = supplier.name;
    form.email = supplier.email || '';
    form.phone = supplier.phone || '';
    form.notes = supplier.notes || '';
    form.errors = {};
    showModal.value = true;
};

// Função para resetar formulário
const resetForm = () => {
    form.name = '';
    form.email = '';
    form.phone = '';
    form.notes = '';
    form.errors = {};
};

// Função para salvar fornecedor
const saveSupplier = () => {
    const url = editingSupplier.value
        ? route('admin.settings.suppliers.update', editingSupplier.value.id)
        : route('admin.settings.suppliers.store');

    const method = editingSupplier.value ? 'put' : 'post';

    router[method](url, {
        name: form.name,
        email: form.email,
        phone: form.phone,
        notes: form.notes,
    }, {
        onSuccess: () => {
            showModal.value = false;
            resetForm();
        },
        onError: (errors) => {
            form.errors = errors;
        }
    });
};

// Função para excluir fornecedor
const deleteSupplier = (supplier: Supplier) => {
    if (confirm(`Tem certeza que deseja excluir o fornecedor "${supplier.name}"?`)) {
        router.delete(route('admin.settings.suppliers.destroy', supplier.id));
    }
};
</script>

<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl text-gray-700 font-bold">Fornecedores</h1>
            <Button @click="openCreateModal" class="flex items-center gap-2">
                <Plus class="w-4 h-4" />
                Novo Fornecedor
            </Button>
        </div>

        <!-- Lista de Fornecedores -->
        <div class="grid gap-4">
            <Card v-for="supplier in props.suppliers" :key="supplier.id">
                <CardHeader>
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold">{{ supplier.name }}</h3>

                            <div class="mt-2 space-y-1">
                                <div v-if="supplier.email" class="flex items-center gap-2 text-sm text-gray-600">
                                    <Mail class="w-3 h-3" />
                                    {{ supplier.email }}
                                </div>

                                <div v-if="supplier.phone" class="flex items-center gap-2 text-sm text-gray-600">
                                    <Phone class="w-3 h-3" />
                                    {{ supplier.phone }}
                                </div>

                                <div v-if="supplier.notes" class="mt-2 text-sm text-gray-500 italic">
                                    {{ supplier.notes }}
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <Button variant="outline" size="sm" @click="openEditModal(supplier)"
                                class="flex items-center gap-1">
                                <Pencil class="w-3 h-3" />
                                Editar
                            </Button>
                            <Button variant="destructive" size="sm" @click="deleteSupplier(supplier)"
                                class="flex items-center gap-1">
                                <Trash2 class="w-3 h-3" />
                                Excluir
                            </Button>
                        </div>
                    </div>
                </CardHeader>
            </Card>

            <div v-if="!props.suppliers || props.suppliers.length === 0" class="text-center py-8 text-gray-500">
                Nenhum fornecedor cadastrado. Clique em "Novo Fornecedor" para começar.
            </div>
        </div>

        <!-- Modal de Criação/Edição -->
        <Dialog v-model:open="showModal">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>
                        {{ editingSupplier ? 'Editar Fornecedor' : 'Novo Fornecedor' }}
                    </DialogTitle>
                </DialogHeader>

                <form @submit.prevent="saveSupplier" class="space-y-4">
                    <div>
                        <Label for="name">Nome do Fornecedor *</Label>
                        <Input id="name" v-model="form.name" placeholder="Nome da empresa ou fornecedor..."
                            :class="{ 'border-red-500': form.errors.name }" />
                        <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label for="email">E-mail</Label>
                            <Input id="email" v-model="form.email" type="email" placeholder="email@exemplo.com"
                                :class="{ 'border-red-500': form.errors.email }" />
                            <p v-if="form.errors.email" class="text-red-500 text-sm mt-1">
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <div>
                            <Label for="phone">Telefone</Label>
                            <Input id="phone" v-model="form.phone" placeholder="(11) 99999-9999"
                                :class="{ 'border-red-500': form.errors.phone }" />
                            <p v-if="form.errors.phone" class="text-red-500 text-sm mt-1">
                                {{ form.errors.phone }}
                            </p>
                        </div>
                    </div>



                    <div>
                        <Label for="notes">Observações</Label>
                        <textarea id="notes" v-model="form.notes" placeholder="Observações adicionais..." rows="3"
                            class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            :class="{ 'border-red-500': form.errors.notes }"></textarea>
                        <p v-if="form.errors.notes" class="text-red-500 text-sm mt-1">
                            {{ form.errors.notes }}
                        </p>
                    </div>

                    <div class="flex justify-end gap-2 pt-4">
                        <Button type="button" variant="outline" @click="showModal = false">
                            Cancelar
                        </Button>
                        <Button type="submit">
                            {{ editingSupplier ? 'Atualizar' : 'Criar' }}
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
