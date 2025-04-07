<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
class GradeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Grade::with(['student:id,first_name,last_name', 'subject:id,name,code']);

         if ($request->has('student_id')) {
             $query->where('student_id', $request->student_id);
         }
         if ($request->has('subject_id')) {
             $query->where('subject_id', $request->subject_id);
         }

        $grades = $query->orderBy('created_at', 'desc')->get();
        return response()->json($grades);
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
    $validator = Validator::make($request->all(), [
        'student_id' => [
            'required',
            'exists:students,id',
            // Verificar que el estudiante esté matriculado en la asignatura
            Rule::exists('enrollments')->where(function ($query) use ($request) {
                $query->where('subject_id', $request->subject_id)
                      ->where('student_id', $request->student_id);
            }),
            // Verificar que no exista calificación previa
            Rule::unique('grades')->where(function ($query) use ($request) {
                return $query->where('subject_id', $request->subject_id)
                             ->where('student_id', $request->student_id);
            }),
        ],
        'subject_id' => 'required|exists:subjects,id',
        'grade'      => 'required|numeric|min:0|max:20',
    ], [
        'student_id.exists' => 'El estudiante no está matriculado en esta asignatura.',
        'student_id.unique' => 'Ya existe una calificación para este estudiante en esta asignatura.'
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $grade = Grade::create($validator->validated());
    $grade->load(['student:id,first_name,last_name', 'subject:id,name,code']);
    return response()->json($grade, 201);
}

    /**
     * Display the specified resource.
     */
    public function show(Grade $grade)
    {

        $grade->load(['student:id,first_name,last_name', 'subject:id,name,code']);
        return response()->json($grade);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function update(Request $request, Grade $grade)
    {
        // Opcional: Autorización para actualizar
        // Gate::authorize('update', $grade);

         $validator = Validator::make($request->all(), [
            'grade' => 'required|numeric|min:0|max:20',
        ]);

         if ($validator->fails()) {
            return response()->json(['message' => 'Error de validación.', 'errors' => $validator->errors()], 422);
        }

        $grade->update($validator->validated());
        $grade->load(['student:id,first_name,last_name', 'subject:id,name,code']);
        return response()->json($grade);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Grade $grade)
    {
        // Opcional: Autorización para eliminar
        // Gate::authorize('delete', $grade);

        $grade->delete();
        // 204 No Content es una respuesta común para DELETE exitoso
        return response()->json(null, 204);
    }
}
