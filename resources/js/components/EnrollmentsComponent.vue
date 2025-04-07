<template>
    <div>
        <h1>Gestión de Matrículas</h1>

        <!-- Sección para Registrar Nueva Matrícula -->
        <div class="form-section">
            <h2>Registrar Nueva Matrícula</h2>
            <!-- Mensaje de éxito general -->
            <div v-if="successMessage" class="alert alert-success">{{ successMessage }}</div>
             <!-- Mensaje de error general -->
            <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>

            <form @submit.prevent="addEnrollment" v-if="!successMessage"> <!-- Oculta form al éxito si quieres -->
                <!-- Selector de Estudiante -->
                <div>
                    <label for="student_id">Estudiante:</label>
                    <select id="student_id" v-model="newEnrollment.student_id" required :disabled="isSubmitting">
                        <option value="" disabled>-- Seleccione Estudiante --</option>
                        <option v-for="student in studentsList" :key="student.id" :value="student.id">
                            {{ student.last_name }}, {{ student.first_name }}
                        </option>
                    </select>
                    <span v-if="formErrors.student_id" class="error-text">{{ formErrors.student_id[0] }}</span>
                </div>

                <!-- Selector de Asignatura -->
                <div>
                    <label for="subject_id">Asignatura:</label>
                    <select id="subject_id" v-model="newEnrollment.subject_id" required :disabled="isSubmitting">
                        <option value="" disabled>-- Seleccione Asignatura --</option>
                        <option v-for="subject in subjectsList" :key="subject.id" :value="subject.id">
                            {{ subject.name }} ({{ subject.code }})
                        </option>
                    </select>
                    <span v-if="formErrors.subject_id" class="error-text">{{ formErrors.subject_id[0] }}</span>
                </div>

                <button type="submit" :disabled="isSubmitting">
                    {{ isSubmitting ? 'Matriculando...' : 'Matricular Estudiante' }}
                </button>
            </form>
             <button v-if="successMessage" @click="resetForm">Registrar otra matrícula</button>
        </div>

        <hr>

        <!-- Sección para Mostrar Matrículas Existentes -->
        <div class="list-section">
            <h2>Matrículas Registradas</h2>
             <div v-if="isLoading">Cargando matrículas...</div>
             <div v-else-if="enrollments.length === 0">No hay matrículas registradas.</div>
             <table v-else class="enrollment-table">
                 <thead>
                     <tr>
                         <th>Estudiante</th>
                         <th>Asignatura</th>
                         <th>Fecha Matrícula</th>
                         <th>Acciones</th>
                     </tr>
                 </thead>
                 <tbody>
                     <tr v-for="enrollment in enrollments" :key="enrollment.id">
                         <td>{{ enrollment.student?.first_name }} {{ enrollment.student?.last_name }}</td>
                         <td>{{ enrollment.subject?.name }} ({{ enrollment.subject?.code }})</td>
                         <td>{{ formatDate(enrollment.created_at) }}</td>
                         <td>
                            <button @click="showEnrollmentDetails(enrollment)" class="action-btn view-btn">Ver</button>
                            <!-- El botón de editar podría llamar a un método 'prepareEdit(enrollment)' -->
                            <!-- <button @click="prepareEdit(enrollment)" class="action-btn edit-btn">Editar</button> -->
                            <button @click="confirmDeleteEnrollment(enrollment.id)" class="action-btn delete-btn">Eliminar</button>
                         </td>
                     </tr>
                 </tbody>
             </table>
             <!-- Aquí podrías añadir controles de paginación si usas ->paginate() -->
        </div>

    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            enrollments: [], // Lista de matrículas existentes
            studentsList: [], // Para el select de estudiantes
            subjectsList: [], // Para el select de asignaturas
            newEnrollment: { // Objeto para el formulario de nueva matrícula
                student_id: '',
                subject_id: ''
            },
            isLoading: false, // Estado de carga para la lista
            isSubmitting: false, // Estado de envío del formulario
            formErrors: {}, // Errores de validación del formulario
            successMessage: '', // Mensaje de éxito
            errorMessage: '', // Mensaje de error general
            // Para paginación (si la implementas):
            // pagination: {}
        };
    },
    methods: {
        // --- Métodos para obtener datos ---
        fetchEnrollments() {
            this.isLoading = true;
            this.errorMessage = ''; // Limpiar errores previos
            axios.get('/api/enrollments') // Ajusta si usas paginación ?page=...
                .then(response => {
                    // Si usas paginación, los datos están en response.data.data
                    this.enrollments = response.data.data || response.data;
                    // Guarda info de paginación si es necesario:
                    // this.pagination = {
                    //    currentPage: response.data.current_page,
                    //    lastPage: response.data.last_page,
                    //    total: response.data.total,
                    // };
                })
                .catch(error => {
                    console.error("Error al obtener matrículas:", error);
                    this.errorMessage = "No se pudieron cargar las matrículas.";
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },
        fetchStudents() {
             axios.get('/api/students')
                .then(response => {
                    this.studentsList = response.data;
                })
                .catch(error => {
                    console.error("Error al obtener estudiantes:", error);
                    // Podrías mostrar un error aquí
                });
        },
        fetchSubjects() {
             axios.get('/api/subjects')
                .then(response => {
                    this.subjectsList = response.data;
                })
                .catch(error => {
                    console.error("Error al obtener asignaturas:", error);
                    // Podrías mostrar un error aquí
                });
        },

        // --- Métodos CRUD ---
        addEnrollment() {
            this.isSubmitting = true;
            this.formErrors = {};
            this.successMessage = '';
            this.errorMessage = '';

            axios.post('/api/enrollments', this.newEnrollment)
                .then(response => {
                    // Añadir la nueva matrícula a la lista (al principio)
                    this.enrollments.unshift(response.data);
                    this.successMessage = "Matrícula registrada exitosamente.";
                    this.resetFormFields(); // Limpiar campos del formulario
                    // Opcional: podrías querer recargar toda la lista
                    // this.fetchEnrollments();
                })
                .catch(error => {
                    console.error("Error al registrar matrícula:", error);
                    if (error.response && error.response.status === 422) {
                        // Errores de validación
                        this.formErrors = error.response.data.errors;
                        this.errorMessage = error.response.data.message || "Por favor, corrige los errores en el formulario.";
                    } else {
                        // Otros errores
                         this.errorMessage = "Ocurrió un error inesperado al registrar la matrícula.";
                         if (error.response && error.response.data && error.response.data.message) {
                             this.errorMessage += ` (${error.response.data.message})`; // Añadir mensaje del backend si existe
                         }
                    }
                })
                .finally(() => {
                    this.isSubmitting = false;
                });
        },

        showEnrollmentDetails(enrollment) {
            // Usamos alert como solicitado para mostrar detalles básicos
            const studentName = enrollment.student ? `${enrollment.student.first_name} ${enrollment.student.last_name}` : 'Desconocido';
            const subjectName = enrollment.subject ? `${enrollment.subject.name} (${enrollment.subject.code})` : 'Desconocida';
            const enrollmentDate = this.formatDate(enrollment.created_at);

            alert(
                `Detalles de la Matrícula:\n` +
                `--------------------------\n` +
                `ID: ${enrollment.id}\n` +
                `Estudiante: ${studentName}\n` +
                `Asignatura: ${subjectName}\n` +
                `Fecha de Registro: ${enrollmentDate}`
            );
            // Alternativa más avanzada: Mostrar esto en un modal.
        },

        confirmDeleteEnrollment(enrollmentId) {
            // Usamos window.confirm para la confirmación
            if (window.confirm("¿Estás seguro de que deseas eliminar esta matrícula? Esta acción no se puede deshacer.")) {
                this.deleteEnrollment(enrollmentId);
            }
        },

        deleteEnrollment(enrollmentId) {
            // Podrías añadir un estado de carga específico para la fila si quieres
            this.errorMessage = ''; // Limpiar errores

            axios.delete(`/api/enrollments/${enrollmentId}`)
                .then(() => {
                    // Eliminar la matrícula de la lista local
                    this.enrollments = this.enrollments.filter(enroll => enroll.id !== enrollmentId);
                    alert("Matrícula eliminada exitosamente."); // Feedback con alert
                    // O podrías usar this.successMessage = "..."
                })
                .catch(error => {
                    console.error(`Error al eliminar matrícula ${enrollmentId}:`, error);
                     this.errorMessage = "No se pudo eliminar la matrícula.";
                      if (error.response && error.response.data && error.response.data.message) {
                         this.errorMessage += ` (${error.response.data.message})`;
                     }
                     alert(this.errorMessage); // Mostrar error con alert
                });
                // .finally(() => { /* quitar estado de carga específico si lo usas */ });
        },

        // --- Métodos Auxiliares ---
        formatDate(dateString) {
            if (!dateString) return 'N/A';
            const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
            return new Date(dateString).toLocaleDateString(undefined, options);
        },
        resetFormFields() {
            this.newEnrollment.student_id = '';
            this.newEnrollment.subject_id = '';
        },
        resetForm() {
             this.resetFormFields();
             this.formErrors = {};
             this.successMessage = '';
             this.errorMessage = '';
             this.isSubmitting = false; // Asegurarse que el botón se rehabilita
        }

    },
    mounted() {
        // Cargar datos iniciales al montar el componente
        this.fetchEnrollments();
        this.fetchStudents();
        this.fetchSubjects();
    }
}
</script>

<style scoped>
/* Estilos generales */
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
button {
    padding: 0.7rem 1.3rem;
    cursor: pointer;
    background-color: #007bff; /* Azul primario */
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 1rem;
    margin-right: 0.5rem; /* Espacio entre botones */
    margin-top: 0.5rem;
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

/* Secciones */
.form-section, .list-section {
    padding: 1.5rem;
    background-color: #f9f9f9;
    border: 1px solid #eee;
    border-radius: 5px;
}

/* Tabla de matrículas */
.enrollment-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}
.enrollment-table th, .enrollment-table td {
    border: 1px solid #ddd;
    padding: 0.8rem;
    text-align: left;
}
.enrollment-table th {
    background-color: #e9ecef;
    font-weight: bold;
}
.enrollment-table tr:nth-child(even) {
    background-color: #f8f9fa;
}

/* Botones de acción en la tabla */
.action-btn {
    padding: 0.3rem 0.6rem;
    font-size: 0.9rem;
    margin-right: 5px; /* Espacio entre botones de acción */
}
.view-btn {
    background-color: #17a2b8; /* Info Cyan */
}
.edit-btn {
    background-color: #ffc107; /* Warning Yellow */
    color: #333;
}
.delete-btn {
    background-color: #dc3545; /* Danger Red */
}

/* Mensajes de error y éxito */
.error-text {
    color: red;
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