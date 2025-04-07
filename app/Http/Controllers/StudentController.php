<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Student::all(), 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:students,email',
        ]);
        
        $student = Student::create($data);
        return response()->json($student, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student)
    {

        return response()->json($student);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Student $student)
    {
        // Podrías añadir autorización aquí
        $validator = Validator::make($request->all(), [
             'first_name' => 'required|string|max:100',
             'last_name' => 'required|string|max:100',
             'email' => [
                 'required',
                 'string',
                 'email',
                 'max:255',
                 Rule::unique('students', 'email')->ignore($student->id),
             ],
         ], [
             // Mismos mensajes que en store
             'first_name.required' => 'El nombre es obligatorio.',
             'last_name.required' => 'El apellido es obligatorio.',
             'email.required' => 'El email es obligatorio.',
             'email.email' => 'Debe ingresar un email válido.',
             'email.unique' => 'Este email ya está registrado.',
             '*.max' => 'El campo :attribute no debe exceder los :max caracteres.',
         ]);

         if ($validator->fails()) {
             return response()->json(['message' => 'Error de validación.', 'errors' => $validator->errors()], 422);
         }

         $student->update($validator->validated());

         return response()->json($student); // Devuelve el estudiante actualizado
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        //
    }

    public function list()
    {
        return response()->json(Student::select('id', 'first_name', 'last_name')->orderBy('last_name')->get());
    }

    public function getStudentReport(Student $student)
    {
        // Podrías añadir autorización aquí

        // Cargar las matrículas con los detalles de la asignatura
        $enrollments = $student->enrollments()
                               ->with('subject:id,name,code') // Carga selectiva de columnas de subject
                               ->get(['id', 'subject_id', 'created_at']); // Carga selectiva de columnas de enrollment

        // Cargar las calificaciones con los detalles de la asignatura
        $grades = $student->grades()
                          ->with('subject:id,name,code') // Carga selectiva
                          ->get(['id', 'subject_id', 'grade', 'created_at', 'updated_at']); // Carga selectiva

        // Estructurar la respuesta
        $reportData = [
            'student' => [ // Incluir info básica del estudiante
                'id' => $student->id,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'email' => $student->email,
            ],
            // Podríamos simplificar esto si solo nos interesan las asignaturas únicas
            // Pero separar puede ser más claro si hay asignaturas matriculadas sin calificar aún
            'subjects_enrolled' => $enrollments->map(function ($enrollment) {
                return [
                    'subject_id' => $enrollment->subject->id,
                    'name' => $enrollment->subject->name,
                    'code' => $enrollment->subject->code,
                    'enrolled_at' => $enrollment->created_at // Fecha de matrícula
                ];
            }),
            'grades_obtained' => $grades->map(function ($grade) {
                 return [
                    'subject_id' => $grade->subject->id,
                    'name' => $grade->subject->name,
                    'code' => $grade->subject->code,
                    'grade' => $grade->grade,
                    'graded_at' => $grade->updated_at // O created_at si prefieres la fecha de registro inicial
                 ];
            }),
        ];

        return response()->json($reportData);
    }
}
