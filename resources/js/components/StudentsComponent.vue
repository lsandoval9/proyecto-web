<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-800">Gestión de Estudiantes</h1>
             <button @click="openAddModal" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Nuevo Estudiante
            </button>
        </div>

        <!-- Mensajes de feedback -->
        <div v-if="message" :class="messageType === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'" class="px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ message }}</span>
             <button @click="message = ''" class="absolute top-0 bottom-0 right-0 px-4 py-3">
                <svg class="fill-current h-6 w-6" :class="messageType === 'success' ? 'text-green-500' : 'text-red-500'" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Cerrar</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.03a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
            </button>
        </div>

        <!-- Tabla para Consultar Estudiantes -->
        <div class="bg-white overflow-hidden shadow-md rounded-lg">
             <div class="px-6 py-4 text-xl font-semibold border-b border-gray-200 bg-gray-50">
                Lista de Estudiantes
            </div>
            <div v-if="isLoadingList" class="p-6 text-center text-gray-500">
                 <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"> <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle> <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path> </svg>
                <p class="mt-2">Cargando estudiantes...</p>
            </div>
            <div v-else-if="students.length === 0" class="p-6 text-center text-gray-500">No hay estudiantes registrados.</div>
            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Apellido</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registrado</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="student in students" :key="student.id" class="hover:bg-gray-50 transition duration-150 ease-in-out">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ student.id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ student.first_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ student.last_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ student.email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(student.created_at) }}</td>
                            <!-- Acciones -->
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <button @click="fetchAndShowReport(student)" class="text-cyan-600 hover:text-cyan-900 focus:outline-none" title="Ver Reporte">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"> <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /> </svg>
                                </button>
                                <button @click="openEditModal(student)" class="text-indigo-600 hover:text-indigo-900 focus:outline-none" title="Editar Estudiante">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" viewBox="0 0 20 20" fill="currentColor"> <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" /> </svg>
                                </button>
                                <button @click="confirmDeleteStudent(student.id)" class="text-red-600 hover:text-red-900 focus:outline-none" title="Eliminar Estudiante">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" viewBox="0 0 20 20" fill="currentColor"> <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /> </svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal para Registrar / Editar Estudiante -->
        <transition name="fade">
            <div v-if="showStudentModal" @click.self="closeStudentModal" class="fixed inset-0 z-40 overflow-y-auto bg-gray-500 bg-opacity-75 transition-opacity flex items-center justify-center" aria-labelledby="modal-student-title" role="dialog" aria-modal="true">
                <div class="bg-white rounded-lg shadow-xl transform transition-all sm:max-w-lg sm:w-full mx-4" @click.stop>
                    <form @submit.prevent="editingStudent ? updateStudent() : addStudent()">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                             <div class="sm:flex sm:items-start">
                                 <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                                     <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"> <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /> </svg>
                                 </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-student-title">
                                        {{ editingStudent ? 'Editar Estudiante' : 'Registrar Nuevo Estudiante' }}
                                    </h3>
                                    <div class="mt-4 space-y-4">
                                        <div>
                                            <label for="modal_first_name" class="block text-sm font-medium text-gray-700">Nombre:</label>
                                            <input type="text" id="modal_first_name" v-model="currentStudent.first_name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <span v-if="errors.first_name" class="text-xs text-red-600">{{ errors.first_name[0] }}</span>
                                        </div>
                                        <div>
                                            <label for="modal_last_name" class="block text-sm font-medium text-gray-700">Apellido:</label>
                                            <input type="text" id="modal_last_name" v-model="currentStudent.last_name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <span v-if="errors.last_name" class="text-xs text-red-600">{{ errors.last_name[0] }}</span>
                                        </div>
                                        <div>
                                            <label for="modal_email" class="block text-sm font-medium text-gray-700">Email:</label>
                                            <input type="email" id="modal_email" v-model="currentStudent.email" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <span v-if="errors.email" class="text-xs text-red-600">{{ errors.email[0] }}</span>
                                        </div>
                                    </div>
                                </div>
                             </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" :disabled="isSaving" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                                {{ isSaving ? (editingStudent ? 'Actualizando...' : 'Guardando...') : (editingStudent ? 'Actualizar' : 'Guardar') }}
                            </button>
                            <button type="button" @click="closeStudentModal" :disabled="isSaving" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </transition>

         <!-- Modal para Reporte del Estudiante -->
        <transition name="fade">
            <div v-if="showReportModal" @click.self="closeReportModal" class="fixed inset-0 z-50 overflow-y-auto bg-gray-500 bg-opacity-75 transition-opacity flex items-center justify-center" aria-labelledby="modal-report-title" role="dialog" aria-modal="true">
                <div class="bg-white rounded-lg shadow-xl transform transition-all sm:max-w-2xl sm:w-full mx-4 max-h-[90vh] overflow-hidden flex flex-col" @click.stop>
                     <!-- Cabecera del Modal Reporte -->
                     <div class="bg-gray-100 px-4 py-3 sm:px-6 border-b border-gray-200">
                          <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-report-title">
                            Reporte Académico - {{ studentReportData?.student?.first_name }} {{ studentReportData?.student?.last_name }}
                          </h3>
                     </div>

                     <!-- Contenido del Modal Reporte -->
                     <div class="px-4 py-5 sm:p-6 overflow-y-auto flex-grow">
                        <div v-if="isLoadingReport" class="text-center py-10">
                            <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"> <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle> <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path> </svg>
                            <p class="mt-2 text-gray-500">Generando reporte...</p>
                        </div>
                        <div v-else-if="!studentReportData" class="text-center py-10 text-red-500">
                            No se pudo cargar la información del reporte.
                        </div>
                        <div v-else class="space-y-6">
                            <!-- Información del Estudiante -->
                            <div>
                                <h4 class="text-md font-semibold text-gray-700 mb-2">Datos del Estudiante</h4>
                                <dl class="grid grid-cols-1 gap-x-4 gap-y-2 sm:grid-cols-2">
                                    <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Nombre Completo</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ studentReportData.student.first_name }} {{ studentReportData.student.last_name }}</dd>
                                    </div>
                                    <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ studentReportData.student.email }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Asignaturas Matriculadas -->
                            <div>
                                <h4 class="text-md font-semibold text-gray-700 mb-2">Asignaturas Matriculadas</h4>
                                <div v-if="studentReportData.subjects_enrolled?.length > 0" class="border rounded-md overflow-hidden">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Matrícula</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="enrollment in studentReportData.subjects_enrolled" :key="`enr-${enrollment.subject_id}`">
                                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 font-mono">{{ enrollment.code }}</td>
                                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ enrollment.name }}</td>
                                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ formatDate(enrollment.enrolled_at) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <p v-else class="text-sm text-gray-500 italic">No hay asignaturas matriculadas registradas.</p>
                            </div>

                            <!-- Calificaciones Obtenidas -->
                             <div>
                                <h4 class="text-md font-semibold text-gray-700 mb-2">Calificaciones Obtenidas</h4>
                                <div v-if="studentReportData.grades_obtained?.length > 0" class="border rounded-md overflow-hidden">
                                     <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asignatura</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Calificación</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Calificación</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="grade in studentReportData.grades_obtained" :key="`grd-${grade.subject_id}`">
                                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 font-mono">{{ grade.code }}</td>
                                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ grade.name }}</td>
                                                <td class="px-4 py-2 whitespace-nowrap text-sm font-semibold" :class="grade.grade >= 10 ? 'text-green-600' : 'text-red-600'">{{ grade.grade }}</td> <!-- Ejemplo de estilo condicional -->
                                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ formatDate(grade.graded_at) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <p v-else class="text-sm text-gray-500 italic">No hay calificaciones registradas.</p>
                            </div>

                        </div>
                     </div>

                    <!-- Pie del Modal Reporte -->
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                        <button type="button" @click="closeReportModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cerrar Reporte
                        </button>
                         <!-- Opcional: Botón para imprimir -->
                         <!-- <button type="button" @click="printReport" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">Imprimir</button> -->
                    </div>
                </div>
            </div>
        </transition>

    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

// --- State ---
const students = ref([]);
const currentStudent = reactive({ id: null, first_name: '', last_name: '', email: '' }); // Para el formulario modal
const editingStudent = ref(null); // Guarda el objeto original al editar
const showStudentModal = ref(false); // Visibilidad modal Add/Edit
const isLoadingList = ref(true); // Cargando lista inicial
const isSaving = ref(false); // Guardando/Actualizando en modal Add/Edit
const message = ref('');
const messageType = ref('success');
const errors = ref({});

// --- State para Reporte ---
const showReportModal = ref(false);
const studentReportData = ref(null);
const isLoadingReport = ref(false);


// --- Métodos API CRUD ---
const fetchStudents = async () => {
    isLoadingList.value = true;
    try {
        const response = await axios.get('/api/students');
        students.value = response.data;
    } catch (error) {
        console.error("Error al obtener estudiantes:", error);
        showFeedback('Error al cargar la lista de estudiantes.', 'error');
    } finally {
        isLoadingList.value = false;
    }
};

const addStudent = async () => {
    isSaving.value = true;
    resetErrorsAndMessages();
    try {
        // Solo enviamos los datos necesarios
        const payload = {
            first_name: currentStudent.first_name,
            last_name: currentStudent.last_name,
            email: currentStudent.email
        };
        const response = await axios.post('/api/students', payload);
        students.value.unshift(response.data); // Añadir al principio
        closeStudentModal();
        showFeedback('Estudiante registrado exitosamente.', 'success');
    } catch (error) {
        handleApiError(error, 'registrar');
    } finally {
        isSaving.value = false;
    }
};

const updateStudent = async () => {
    if (!editingStudent.value) return;
    isSaving.value = true;
    resetErrorsAndMessages();
    try {
         const payload = {
            first_name: currentStudent.first_name,
            last_name: currentStudent.last_name,
            email: currentStudent.email
        };
        const response = await axios.put(`/api/students/${editingStudent.value.id}`, payload);
        const index = students.value.findIndex(s => s.id === editingStudent.value.id);
        if (index !== -1) {
            students.value[index] = response.data;
        } else {
            fetchStudents(); // Fallback
        }
        closeStudentModal();
        showFeedback('Estudiante actualizado exitosamente.', 'success');
    } catch (error) {
        handleApiError(error, 'actualizar');
    } finally {
        isSaving.value = false;
    }
};

const deleteStudent = async (studentId) => {
    resetErrorsAndMessages();
    try {
        await axios.delete(`/api/students/${studentId}`);
        students.value = students.value.filter(s => s.id !== studentId);
        showFeedback('Estudiante eliminado exitosamente.', 'success');
    } catch (error) {
        console.error(`Error al eliminar estudiante ${studentId}:`, error);
        let errorMsg = 'Error al eliminar el estudiante.';
        if (error.response && error.response.data && error.response.data.message) {
            errorMsg = error.response.data.message; // Mensaje específico (ej. 409)
        }
        showFeedback(errorMsg, 'error');
    }
};

// --- Métodos API Reporte ---
const fetchAndShowReport = async (student) => {
    isLoadingReport.value = true;
    studentReportData.value = null; // Limpiar datos previos
    showReportModal.value = true; // Abrir modal inmediatamente
    message.value = ''; // Limpiar mensaje global

    try {
        const response = await axios.get(`/api/students/${student.id}/report`);
        studentReportData.value = response.data;
    } catch (error) {
        console.error(`Error al obtener reporte para estudiante ${student.id}:`, error);
        showFeedback(`Error al generar el reporte para ${student.first_name}.`, 'error');
        // Mantener modal abierto para mostrar mensaje de error interno si es necesario
        studentReportData.value = null; // Asegurar que no se muestre data parcial
    } finally {
        isLoadingReport.value = false;
    }
};


// --- Métodos UI & Auxiliares ---
const openAddModal = () => {
    resetFormAndErrors();
    editingStudent.value = null;
    showStudentModal.value = true;
};

const openEditModal = (student) => {
    resetFormAndErrors();
    editingStudent.value = { ...student };
    currentStudent.id = student.id;
    currentStudent.first_name = student.first_name;
    currentStudent.last_name = student.last_name;
    currentStudent.email = student.email;
    showStudentModal.value = true;
};

const closeStudentModal = () => {
    showStudentModal.value = false;
    // Resetear formulario al cerrar por si acaso
    // resetFormAndErrors();
};

const closeReportModal = () => {
    showReportModal.value = false;
    studentReportData.value = null; // Limpiar datos al cerrar
};


const confirmDeleteStudent = (studentId) => {
    if (window.confirm('¿Estás seguro de que deseas eliminar este estudiante? Esta acción no se puede deshacer y eliminará sus datos asociados si no están protegidos por restricciones.')) {
        deleteStudent(studentId);
    }
};

const resetFormAndErrors = () => {
    currentStudent.id = null;
    currentStudent.first_name = '';
    currentStudent.last_name = '';
    currentStudent.email = '';
    errors.value = {};
    // No limpiar mensaje global aquí, podría haber un mensaje de éxito/error de otra operación
};

const resetErrorsAndMessages = () => {
     errors.value = {};
     message.value = '';
};


const handleApiError = (error, action = 'realizar la operación') => {
    if (error.response && error.response.status === 422) {
        errors.value = error.response.data.errors;
        // Mostrar mensaje global indicando revisar formulario
         showFeedback(`Por favor, corrige los errores en el formulario al ${action}.`, 'error');
    } else {
        console.error(`Error al ${action} estudiante:`, error);
        let errorMsg = `Error al ${action} el estudiante.`;
        if (error.response && error.response.data && error.response.data.message) {
             errorMsg = error.response.data.message;
        }
        showFeedback(errorMsg, 'error');
         // Considera cerrar el modal add/edit en errores inesperados
         // if (showStudentModal.value) closeStudentModal();
    }
};

const showFeedback = (msg, type = 'success') => {
    message.value = msg;
    messageType.value = type;
    setTimeout(() => {
        if (message.value === msg) { message.value = ''; }
    }, 5000);
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    try {
        const options = { year: 'numeric', month: '2-digit', day: '2-digit' };
        return new Date(dateString).toLocaleDateString('es-ES', options);
    } catch(e){ return dateString; }
};

// --- Ciclo de Vida ---
onMounted(() => {
    fetchStudents();
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

/* Cierre de modal al clickear fondo */
.fixed.inset-0 { cursor: pointer; }
.fixed.inset-0 > div { cursor: default; }

button:disabled { cursor: not-allowed; }
.space-x-2 > * + * { margin-left: 0.5rem; }

/* Ajuste para scroll en modal de reporte */
.max-h-\[90vh\] { max-height: 90vh; }
.flex-col { display: flex; flex-direction: column; }
.flex-grow { flex-grow: 1; }
</style>