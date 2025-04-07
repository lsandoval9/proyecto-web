<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Student; // Necesario para lista de estudiantes
use App\Models\Subject; // Necesario para lista de asignaturas
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; // Necesario para reglas avanzadas
use Illuminate\Support\Facades\Validator; // Para manejo explícito de validación y errores

class EnrollmentController extends Controller
{
    /**
     * Display a listing of the resource.
     * Opcionalmente filtra por estudiante o asignatura.
     */
    public function index(Request $request)
    {
        // Podrías añadir autorización aquí si es necesario
        // Gate::authorize('viewAny', Enrollment::class);

        $query = Enrollment::with(['student:id,first_name,last_name', 'subject:id,name,code']);

         if ($request->has('student_id')) {
             $query->where('student_id', $request->student_id);
         }
         if ($request->has('subject_id')) {
             $query->where('subject_id', $request->subject_id);
         }

        // Considera paginación para listas largas
        $enrollments = $query->orderBy('created_at', 'desc')->paginate(20);
        // $enrollments = $query->orderBy('created_at', 'desc')->get(); // Sin paginación

        return response()->json($enrollments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Podrías añadir autorización aquí
        // Gate::authorize('create', Enrollment::class);

         $validator = Validator::make($request->all(), [
            'student_id' => [
                'required',
                'exists:students,id',
                // Regla para evitar duplicados: el par student_id y subject_id debe ser único
                Rule::unique('enrollments')->where(function ($query) use ($request) {
                    return $query->where('subject_id', $request->subject_id);
                }),
            ],
            'subject_id' => 'required|exists:subjects,id',
        ], [
            // Mensajes personalizados
            'student_id.required' => 'Debes seleccionar un estudiante.',
            'student_id.exists' => 'El estudiante seleccionado no es válido.',
            'student_id.unique' => 'Este estudiante ya está matriculado en esta asignatura.',
            'subject_id.required' => 'Debes seleccionar una asignatura.',
            'subject_id.exists' => 'La asignatura seleccionada no es válida.',
        ]);

        if ($validator->fails()) {
            // Devolver errores de validación claros
            return response()->json(['message' => 'Error de validación.', 'errors' => $validator->errors()], 422);
        }

        $enrollment = Enrollment::create($validator->validated());
        // Cargar relaciones para devolver la info completa al frontend
        $enrollment->load(['student:id,first_name,last_name', 'subject:id,name,code']);

        return response()->json($enrollment, 201); // 201 Created
    }

    /**
     * Display the specified resource.
     */
    public function show(Enrollment $enrollment)
    {
        // Podrías añadir autorización aquí
        // Gate::authorize('view', $enrollment);

        // Cargar relaciones por si no vienen ya cargadas (depende de cómo se llame)
        $enrollment->load(['student:id,first_name,last_name', 'subject:id,name,code']);
        return response()->json($enrollment);
    }

    /**
     * Update the specified resource in storage.
     * Nota: Actualizar una matrícula (cambiar estudiante o asignatura) es raro.
     * Normalmente se elimina y se crea una nueva.
     * Este método podría servir si tuvieras otros campos (ej. fecha, estado).
     * Por ahora, lo dejaremos simple, sin permitir cambiar student/subject.
     */
    public function update(Request $request, Enrollment $enrollment)
    {
         // Podrías añadir autorización aquí
        // Gate::authorize('update', $enrollment);

        // Ejemplo: Si tuvieras un campo 'status' que quisieras actualizar
        /*
        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|required|string|in:active,inactive,completed', // Ejemplo
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Error de validación.', 'errors' => $validator->errors()], 422);
        }

        $enrollment->update($validator->validated());
        */

        // Como no hay campos actualizables definidos, simplemente devolvemos la matrícula existente.
        // O podrías devolver un error 405 Method Not Allowed si no se permite la actualización.
        $enrollment->load(['student:id,first_name,last_name', 'subject:id,name,code']);
        return response()->json($enrollment); // Devolvemos la matrícula sin cambios o actualizada si hubiera campos
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Enrollment $enrollment)
    {
        // Podrías añadir autorización aquí
        // Gate::authorize('delete', $enrollment);

        try {
            $enrollment->delete();
            // 204 No Content es estándar para un DELETE exitoso sin contenido que devolver
            return response()->json(null, 204);
        } catch (\Exception $e) {
             // Capturar errores, por ejemplo, si hay restricciones de clave externa (calificaciones asociadas)
             // Log::error("Error al eliminar matrícula {$enrollment->id}: " . $e->getMessage()); // Necesitas use Illuminate\Support\Facades\Log;
            return response()->json(['message' => 'No se pudo eliminar la matrícula.', 'error' => $e->getMessage()], 500); // Error de servidor
        }
    }

     // --- Métodos Adicionales para Selects del Frontend ---
     // (Reutilizados de la respuesta anterior, asegúrate que existen o créalos)

    /**
     * Devuelve una lista de estudiantes para usar en selects.
     */
    public function getStudentsList()
    {
        // Añadir lógica de autorización si es necesario
        $students = Student::select('id', 'first_name', 'last_name')
                           ->orderBy('last_name')
                           ->orderBy('first_name')
                           ->get();
        return response()->json($students);
    }

     /**
     * Devuelve una lista de asignaturas para usar en selects.
     */
    public function getSubjectsList()
    {
         // Añadir lógica de autorización si es necesario
        $subjects = Subject::select('id', 'name', 'code')
                           ->orderBy('name')
                           ->get();
        return response()->json($subjects);
    }
}