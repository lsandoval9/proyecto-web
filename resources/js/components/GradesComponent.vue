<template>
    <div>
        <h1>Gestión de Calificaciones</h1>

        <!-- Sección para Registrar o Editar Calificación -->
        <div class="form-section">
            <h2>{{ editingGrade ? 'Editar Calificación' : 'Registrar Nueva Calificación' }}</h2>

            <!-- Mensaje de éxito -->
            <div v-if="successMessage" class="alert alert-success">{{ successMessage }}</div>
             <!-- Mensaje de error general -->
            <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>

            <!-- Formulario: Usamos v-if para no mostrarlo mientras hay mensaje de éxito -->
            <form @submit.prevent="editingGrade ? updateGrade() : addGrade()" v-if="!successMessage">
                <!-- Selector de Estudiante (Deshabilitado en modo edición) -->
                <div>
                    <label for="student_id">Estudiante:</label>
                    <select id="student_id" v-model="currentGrade.student_id" required :disabled="isSubmitting || !!editingGrade">
                        <option value="" disabled>-- Seleccione Estudiante --</option>
                        <option v-for="student in studentsList" :key="student.id" :value="student.id">
                            {{ student.last_name }}, {{ student.first_name }}
                        </option>
                    </select>
                     <!-- Mostrar nombre si está en modo edición -->
                    <span v-if="editingGrade" class="readonly-info">
                        {{ editingGrade.student?.last_name }}, {{ editingGrade.student?.first_name }}
                    </span>
                    <span v-if="formErrors.student_id" class="error-text">{{ formErrors.student_id[0] }}</span>
                </div>

                <!-- Selector de Asignatura (Deshabilitado en modo edición) -->
                <div>
                    <label for="subject_id">Asignatura:</label>
                    <select id="subject_id" v-model="currentGrade.subject_id" required :disabled="isSubmitting || !!editingGrade">
                        <option value="" disabled>-- Seleccione Asignatura --</option>
                        <option v-for="subject in subjectsList" :key="subject.id" :value="subject.id">
                            {{ subject.name }} ({{ subject.code }})
                        </option>
                    </select>
                     <!-- Mostrar nombre si está en modo edición -->
                    <span v-if="editingGrade" class="readonly-info">
                        {{ editingGrade.subject?.name }} ({{ editingGrade.subject?.code }})
                    </span>
                    <span v-if="formErrors.subject_id" class="error-text">{{ formErrors.subject_id[0] }}</span>
                </div>

                <!-- Input de Calificación (Editable en ambos modos) -->
                <div>
                    <label for="grade">Calificación (0-20):</label>
                    <input type="number" id="grade" step="0.01" min="0" max="20" v-model="currentGrade.grade" required :disabled="isSubmitting">
                    <span v-if="formErrors.grade" class="error-text">{{ formErrors.grade[0] }}</span>
                </div>

                <!-- Botones de Acción del Formulario -->
                <div class="form-actions">
                    <button type="submit" :disabled="isSubmitting">
                        {{ isSubmitting ? (editingGrade ? 'Actualizando...' : 'Registrando...') : (editingGrade ? 'Actualizar Calificación' : 'Registrar Calificación') }}
                    </button>
                    <button type="button" v-if="editingGrade" @click="cancelEdit" :disabled="isSubmitting" class="btn-secondary">
                        Cancelar Edición
                    </button>
                </div>
            </form>

            <!-- Botón para mostrar el formulario de nuevo después de un éxito -->
             <button v-if="successMessage" @click="resetForm(true)">Registrar/Editar otra</button>
        </div>

        <hr>

        <!-- Sección para Mostrar Calificaciones Existentes -->
        <div class="list-section">
            <h2>Calificaciones Registradas</h2>
            <div v-if="isLoadingGrades">Cargando calificaciones...</div>
            <div v-else-if="grades.length === 0">No hay calificaciones registradas.</div>
            <table v-else class="data-table">
                <thead>
                    <tr>
                        <th>Estudiante</th>
                        <th>Asignatura</th>
                        <th>Calificación</th>
                        <th>Fecha Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="grade in grades" :key="grade.id">
                        <td>{{ grade.student?.first_name }} {{ grade.student?.last_name }}</td>
                        <td>{{ grade.subject?.name }} ({{ grade.subject?.code }})</td>
                        <td><strong>{{ grade.grade }}</strong></td>
                        <td>{{ formatDate(grade.created_at) }}</td>
                        <td>
                            <button @click="showGradeDetails(grade)" class="action-btn view-btn" title="Ver Detalles">Ver</button>
                            <button @click="prepareEdit(grade)" class="action-btn edit-btn" title="Editar Calificación">Editar</button>
                            <button @click="confirmDeleteGrade(grade.id)" class="action-btn delete-btn" title="Eliminar Calificación">Eliminar</button>
                        </td>
                    </tr>
                </tbody>
            </table>
             <!-- Controles de paginación (si se implementan) -->
             <!-- <div v-if="pagination.lastPage > 1"> ... </div> -->
        </div>

    </div>
</template>

<script>
import axios from 'axios';

// Objeto vacío para el estado inicial del formulario
const initialGradeState = {
    student_id: '',
    subject_id: '',
    grade: ''
};

export default {
    data() {
        return {
            grades: [], // Lista de calificaciones
            studentsList: [], // Para select estudiantes
            subjectsList: [], // Para select asignaturas
            currentGrade: { ...initialGradeState }, // Datos del formulario (para crear o editar)
            editingGrade: null, // Guarda la calificación que se está editando, o null si se está creando
            isLoadingGrades: false, // Cargando lista
            isSubmitting: false,    // Enviando formulario (crear/editar)
            formErrors: {},         // Errores de validación del formulario
            successMessage: '',     // Mensaje de éxito
            errorMessage: '',       // Mensaje de error general
            // pagination: {}       // Para info de paginación si se usa
        };
    },
    methods: {
        // --- Obtener Datos ---
        fetchGrades() {
            this.isLoadingGrades = true;
            this.errorMessage = '';
            axios.get('/api/grades') // Ajustar con parámetros de paginación si es necesario
                .then(response => {
                    this.grades = response.data.data || response.data; // Adaptar si hay paginación
                    // this.pagination = response.data; // Guardar datos de paginación
                })
                .catch(error => {
                    console.error("Error al obtener calificaciones:", error);
                    this.errorMessage = "No se pudieron cargar las calificaciones.";
                })
                .finally(() => {
                    this.isLoadingGrades = false;
                });
        },
        fetchStudents() {
             // Asegúrate que la ruta es correcta, puede ser '/api/select/students'
             axios.get('/api/students')
                .then(response => { this.studentsList = response.data; })
                .catch(error => console.error("Error al obtener estudiantes:", error));
        },
        fetchSubjects() {
             // Asegúrate que la ruta es correcta, puede ser '/api/select/subjects'
             axios.get('/api/subjects')
                .then(response => { this.subjectsList = response.data; })
                .catch(error => console.error("Error al obtener asignaturas:", error));
        },

        // --- CRUD ---
        addGrade() {
            this.isSubmitting = true;
            this.resetMessagesAndErrors();

            axios.post('/api/grades', this.currentGrade)
                .then(response => {
                    this.grades.unshift(response.data); // Añadir al inicio de la lista
                    this.successMessage = '¡Calificación registrada exitosamente!';
                    this.resetForm(); // Limpiar formulario para siguiente registro
                })
                .catch(this.handleApiError)
                .finally(() => { this.isSubmitting = false; });
        },

        prepareEdit(grade) {
            this.resetMessagesAndErrors();
            // Clonar el objeto para evitar modificar el original directamente en la lista
            this.editingGrade = { ...grade };
            // Cargar datos en el formulario
            this.currentGrade = {
                student_id: grade.student_id,
                subject_id: grade.subject_id,
                grade: grade.grade
             };
             // Opcional: scroll al formulario
             // this.$el.querySelector('.form-section').scrollIntoView({ behavior: 'smooth' });
        },

        updateGrade() {
            if (!this.editingGrade) return; // Seguridad

            this.isSubmitting = true;
            this.resetMessagesAndErrors();

            // Solo enviamos el campo 'grade' ya que es lo único editable según el controlador
            const payload = { grade: this.currentGrade.grade };

            axios.put(`/api/grades/${this.editingGrade.id}`, payload)
                .then(response => {
                    // Buscar el índice de la calificación actualizada en la lista
                    const index = this.grades.findIndex(g => g.id === this.editingGrade.id);
                    if (index !== -1) {
                        // Actualizar la calificación en la lista local con los datos del servidor
                        this.grades.splice(index, 1, response.data);
                    } else {
                        // Si no se encontró (raro), recargar la lista completa
                        this.fetchGrades();
                    }
                    this.successMessage = '¡Calificación actualizada exitosamente!';
                    this.resetForm(); // Salir del modo edición
                })
                .catch(this.handleApiError)
                .finally(() => { this.isSubmitting = false; });
        },

        cancelEdit() {
            this.resetForm();
        },

        showGradeDetails(grade) {
            // Usar alert para detalles básicos
            const studentName = grade.student ? `${grade.student.first_name} ${grade.student.last_name}` : 'N/A';
            const subjectName = grade.subject ? `${grade.subject.name} (${grade.subject.code})` : 'N/A';
            const gradeDate = this.formatDate(grade.created_at);
            const updatedDate = this.formatDate(grade.updated_at);

            alert(
                `Detalles de la Calificación:\n` +
                `--------------------------\n` +
                `ID: ${grade.id}\n` +
                `Estudiante: ${studentName}\n` +
                `Asignatura: ${subjectName}\n` +
                `Calificación: ${grade.grade}\n` +
                `Registrada: ${gradeDate}\n` +
                (grade.created_at !== grade.updated_at ? `Última Actualización: ${updatedDate}` : '') // Mostrar si fue actualizada
            );
        },

        confirmDeleteGrade(gradeId) {
            if (window.confirm("¿Estás seguro de que deseas eliminar esta calificación? Esta acción no se puede deshacer.")) {
                this.deleteGrade(gradeId);
            }
        },

        deleteGrade(gradeId) {
            this.resetMessagesAndErrors();
            // Opcional: Indicar visualmente que se está eliminando esa fila

            axios.delete(`/api/grades/${gradeId}`)
                .then(() => {
                    // Eliminar de la lista local
                    this.grades = this.grades.filter(g => g.id !== gradeId);
                    // Usar alert para éxito, o this.successMessage
                     alert("Calificación eliminada exitosamente.");
                     // this.successMessage = "Calificación eliminada exitosamente.";
                })
                .catch(error => {
                    console.error(`Error al eliminar calificación ${gradeId}:`, error);
                    this.errorMessage = "No se pudo eliminar la calificación.";
                    if (error.response && error.response.data && error.response.data.message) {
                        this.errorMessage += ` (${error.response.data.message})`;
                    }
                    alert(this.errorMessage); // Mostrar error con alert
                });
                // .finally(() => { /* quitar indicador visual si se usó */ });
        },

        // --- Métodos Auxiliares ---
        handleApiError(error) {
             console.error("Error en la operación:", error);
             if (error.response) {
                 if (error.response.status === 422) {
                    // Errores de validación
                    this.formErrors = error.response.data.errors;
                    this.errorMessage = error.response.data.message || "Por favor, corrige los errores indicados.";
                 } else {
                     // Otros errores del servidor (permisos, error interno, etc.)
                     this.errorMessage = error.response.data.message || `Ocurrió un error (${error.response.status}).`;
                 }
             } else if (error.request) {
                 // Error de red o no hubo respuesta
                 this.errorMessage = "No se pudo conectar con el servidor. Verifica tu conexión.";
             } else {
                 // Error en la configuración de la petición
                 this.errorMessage = "Ocurrió un error inesperado al preparar la solicitud.";
             }
        },
        resetForm(clearMessages = false) {
            this.currentGrade = { ...initialGradeState };
            this.editingGrade = null;
            this.formErrors = {};
            if (clearMessages) {
                 this.successMessage = '';
                 this.errorMessage = '';
            }
        },
         resetMessagesAndErrors() {
            this.successMessage = '';
            this.errorMessage = '';
            this.formErrors = {};
        },
        formatDate(dateString) {
            if (!dateString) return 'N/A';
            try {
                const options = { year: 'numeric', month: 'short', day: 'numeric', /* hour: '2-digit', minute: '2-digit' */ };
                return new Date(dateString).toLocaleDateString(undefined, options);
            } catch (e) {
                return dateString; // Devolver original si falla el parseo
            }
        }
    },
    mounted() {
        this.fetchGrades();
        this.fetchStudents();
        this.fetchSubjects();
    }
}
</script>

<style scoped>
/* Reutilizar y adaptar estilos de la versión de Enrollments */
div {
    margin-bottom: 1.5rem;
}
label {
    display: block;
    margin-bottom: 0.3rem;
    font-weight: bold;
}
input, select {
    margin-bottom: 0.25rem;
    padding: 0.6rem;
    width: 100%;
    box-sizing: border-box;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 1rem;
}
/* Estilo para mostrar info no editable en modo edición */
.readonly-info {
    display: block;
    padding: 0.6rem;
    background-color: #e9ecef; /* Gris claro */
    border: 1px solid #ced4da;
    border-radius: 4px;
    margin-top: -0.25rem; /* Ajustar espacio */
    font-style: italic;
    color: #495057;
}

button {
    padding: 0.7rem 1.3rem;
    cursor: pointer;
    background-color: #28a745; /* Verde para calificaciones */
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 1rem;
    margin-right: 0.5rem;
    margin-top: 0.5rem;
}
button.btn-secondary {
     background-color: #6c757d; /* Gris secundario */
}
button:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}
hr {
    margin: 2rem 0;
    border-top: 1px solid #eee;
}
h1, h2 {
    color: #333;
    margin-bottom: 1rem;
}

.form-section, .list-section {
    padding: 1.5rem;
    background-color: #f9f9f9;
    border: 1px solid #eee;
    border-radius: 5px;
}
.form-actions {
    margin-top: 1rem;
}

/* Tabla de datos */
.data-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}
.data-table th, .data-table td {
    border: 1px solid #ddd;
    padding: 0.8rem;
    text-align: left;
    vertical-align: middle;
}
.data-table th {
    background-color: #e9ecef;
    font-weight: bold;
}
.data-table tr:nth-child(even) {
    background-color: #f8f9fa;
}

/* Botones de acción */
.action-btn {
    padding: 0.3rem 0.6rem;
    font-size: 0.85rem;
    margin-right: 5px;
    vertical-align: middle;
}
.view-btn { background-color: #17a2b8; } /* Info Cyan */
.edit-btn { background-color: #ffc107; color: #333; } /* Warning Yellow */
.delete-btn { background-color: #dc3545; } /* Danger Red */

/* Mensajes */
.error-text {
    color: #dc3545; /* Danger Red */
    font-size: 0.85em;
    display: block;
    margin-top: -0.1rem;
    margin-bottom: 0.5rem;
}
.alert {
    padding: 0.8rem;
    margin-bottom: 1rem;
    border: 1px solid transparent;
    border-radius: 4px;
}
.alert-success {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
}
.alert-danger {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}
</style>