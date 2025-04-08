<template>

    <Head title="Gestion de Materias" />

    <AuthenticatedLayout>

        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Matriculas
            </h2>
        </template>
        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <button @click="openAddModal" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Nueva Asignatura
                </button>
            </div>

            <!-- Mensajes de feedback globales -->
            <div v-if="message" :class="messageType === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'" class="px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ message }}</span>
            </div>

            <!-- Tabla para Consultar Asignaturas -->
            <div class="bg-white overflow-hidden shadow-md rounded-lg">
                <div class="px-6 py-4 text-xl font-semibold border-b border-gray-200 bg-gray-50">
                    Lista de Asignaturas
                </div>
                <div v-if="isLoadingList" class="p-6 text-center text-gray-500">
                    <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="mt-2">Cargando asignaturas...</p>
                </div>
                <div v-else-if="subjects.length === 0" class="p-6 text-center text-gray-500">No hay asignaturas registradas.</div>
                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registrada</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="subject in subjects" :key="subject.id" class="hover:bg-gray-50 transition duration-150 ease-in-out">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ subject.id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ subject.name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">{{ subject.code }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(subject.created_at) }}</td>
                            <!-- Acciones Edit/Delete -->
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <button @click="openEditModal(subject)" class="text-indigo-600 hover:text-indigo-900 focus:outline-none" title="Editar Asignatura">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                    </svg>
                                </button>
                                <button @click="confirmDeleteSubject(subject.id)" class="text-red-600 hover:text-red-900 focus:outline-none" title="Eliminar Asignatura">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal para Registrar / Editar Asignatura -->
            <transition name="fade">
                <div v-if="showSubjectModal" @click.self="closeSubjectModal" class="fixed inset-0 z-50 overflow-y-auto bg-gray-500 bg-opacity-75 transition-opacity flex items-center justify-center" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="bg-white rounded-lg shadow-xl transform transition-all sm:max-w-lg sm:w-full mx-4" @click.stop>
                        <!-- Formulario dentro del modal -->
                        <form @submit.prevent="editingSubject ? updateSubject() : addSubject()">
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                                        <svg class="h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                    </div>
                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                            {{ editingSubject ? 'Editar Asignatura' : 'Registrar Nueva Asignatura' }}
                                        </h3>
                                        <div class="mt-4 space-y-4">
                                            <div>
                                                <label for="modal_name" class="block text-sm font-medium text-gray-700">Nombre:</label>
                                                <input type="text" id="modal_name" v-model="currentSubject.name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Ej: Cálculo I">
                                                <span v-if="errors.name" class="text-xs text-red-600">{{ errors.name[0] }}</span>
                                            </div>
                                            <div>
                                                <label for="modal_code" class="block text-sm font-medium text-gray-700">Código Único:</label>
                                                <input type="text" id="modal_code" v-model="currentSubject.code" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Ej: MA1111">
                                                <span v-if="errors.code" class="text-xs text-red-600">{{ errors.code[0] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button type="submit" :disabled="isSaving" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                                    {{ isSaving ? (editingSubject ? 'Actualizando...' : 'Guardando...') : (editingSubject ? 'Actualizar' : 'Guardar') }}
                                </button>
                                <button type="button" @click="closeSubjectModal" :disabled="isSaving" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </transition>

        </div>
            </div>
        </div>
    </AuthenticatedLayout>


</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import {Head} from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

// --- State ---
const subjects = ref([]); // Lista de asignaturas
const currentSubject = reactive({ id: null, name: '', code: '' }); // Datos del formulario modal
const editingSubject = ref(null); // Guarda el objeto original al editar, o null si es nuevo
const showSubjectModal = ref(false); // Visibilidad del modal
const isLoadingList = ref(true); // Cargando la lista inicial
const isSaving = ref(false); // Guardando/Actualizando en el modal
const message = ref(''); // Mensaje global de feedback
const messageType = ref('success'); // Tipo de mensaje: 'success' o 'error'
const errors = ref({}); // Errores de validación del formulario

// --- Métodos API ---
const fetchSubjects = async () => {
    isLoadingList.value = true;
    try {
        const response = await axios.get('/api/subjects');
        subjects.value = response.data;
    } catch (error) {
        console.error("Error al obtener asignaturas:", error);
        showFeedback('Error al cargar la lista de asignaturas.', 'error');
    } finally {
        isLoadingList.value = false;
    }
};

const addSubject = async () => {
    isSaving.value = true;
    errors.value = {};
    message.value = '';
    try {
        const response = await axios.post('/api/subjects', { name: currentSubject.name, code: currentSubject.code });
        subjects.value.unshift(response.data); // Añadir al principio
        closeSubjectModal();
        showFeedback('Asignatura registrada exitosamente.', 'success');
    } catch (error) {
        handleApiError(error, 'registrar');
    } finally {
        isSaving.value = false;
    }
};

const updateSubject = async () => {
    if (!editingSubject.value) return;
    isSaving.value = true;
    errors.value = {};
    message.value = '';
    try {
        const response = await axios.put(`/api/subjects/${editingSubject.value.id}`, { name: currentSubject.name, code: currentSubject.code });
        // Actualizar en la lista
        const index = subjects.value.findIndex(s => s.id === editingSubject.value.id);
        if (index !== -1) {
            subjects.value[index] = response.data;
        } else {
            fetchSubjects(); // Fallback si no se encuentra
        }
        closeSubjectModal();
        showFeedback('Asignatura actualizada exitosamente.', 'success');
    } catch (error) {
        handleApiError(error, 'actualizar');
    } finally {
        isSaving.value = false;
    }
};

const deleteSubject = async (subjectId) => {
    // No necesita confirmación aquí, se hace en confirmDeleteSubject
    message.value = ''; // Limpiar mensajes previos
    try {
        await axios.delete(`/api/subjects/${subjectId}`);
        // Eliminar de la lista local
        subjects.value = subjects.value.filter(s => s.id !== subjectId);
        showFeedback('Asignatura eliminada exitosamente.', 'success');
    } catch (error) {
        console.error(`Error al eliminar asignatura ${subjectId}:`, error);
        let errorMsg = 'Error al eliminar la asignatura.';
        // Intentar obtener mensaje específico del backend (ej: 409 Conflict)
        if (error.response && error.response.data && error.response.data.message) {
            errorMsg = error.response.data.message;
        }
        showFeedback(errorMsg, 'error');
    }
};


// --- Métodos UI & Auxiliares ---
const openAddModal = () => {
    resetFormAndErrors();
    editingSubject.value = null; // Asegurar que estamos en modo 'añadir'
    showSubjectModal.value = true;
};

const openEditModal = (subject) => {
    resetFormAndErrors();
    editingSubject.value = { ...subject }; // Guardar el original
    // Poblar formulario reactivo
    currentSubject.id = subject.id;
    currentSubject.name = subject.name;
    currentSubject.code = subject.code;
    showSubjectModal.value = true;
};

const closeSubjectModal = () => {
    showSubjectModal.value = false;
    // Podríamos resetear el formulario aquí también por si acaso
    // resetFormAndErrors();
};

const confirmDeleteSubject = (subjectId) => {
    if (window.confirm('¿Estás seguro de que deseas eliminar esta asignatura? Esta acción no se puede deshacer y podría afectar matrículas o calificaciones existentes.')) {
        deleteSubject(subjectId);
    }
};

const resetFormAndErrors = () => {
    currentSubject.id = null;
    currentSubject.name = '';
    currentSubject.code = '';
    errors.value = {};
    message.value = ''; // Limpiar mensaje global al abrir modal o resetear
};

const handleApiError = (error, action = 'realizar la operación') => {
    if (error.response && error.response.status === 422) {
        // Error de validación
        errors.value = error.response.data.errors;
        // No mostramos mensaje global aquí, los errores están en los campos
        // showFeedback(`Por favor, corrige los errores al ${action}.`, 'error', false);
    } else {
        console.error(`Error al ${action} asignatura:`, error);
        let errorMsg = `Error al ${action} la asignatura.`;
        if (error.response && error.response.data && error.response.data.message) {
             errorMsg = error.response.data.message; // Usar mensaje del backend si existe
        }
        showFeedback(errorMsg, 'error');
        // Cerrar modal en caso de error inesperado que no sea validación
        // closeSubjectModal();
    }
};

const showFeedback = (msg, type = 'success') => {
    message.value = msg;
    messageType.value = type;
    // Ocultar mensaje después de 5 segundos
    setTimeout(() => {
        if (message.value === msg) { // Solo si no ha cambiado mientras tanto
             message.value = '';
        }
    }, 5000);
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    try {
        const options = { year: 'numeric', month: '2-digit', day: '2-digit' };
        return new Date(dateString).toLocaleDateString('es-ES', options);
    } catch(e){
        return dateString; // fallback
    }
};

// --- Ciclo de Vida ---
onMounted(() => {
    fetchSubjects();
});
</script>

<style scoped>
/* Estilos para la transición del modal (fade) */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Asegurar que el click en el fondo cierra el modal */
.fixed.inset-0 {
  cursor: pointer;
}
.fixed.inset-0 > div {
  cursor: default;
}

/* Pequeños ajustes si son necesarios */
button:disabled {
  cursor: not-allowed;
}
.space-x-2 > * + * {
    margin-left: 0.5rem; /* Ajustar espacio entre botones de acción */
}

</style>
