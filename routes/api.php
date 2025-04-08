<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\GradeController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {

    Route::get("/", function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/students', [StudentController::class, 'index']);
    Route::post('/students', [StudentController::class, 'store']);
    Route::get('/students/list', [StudentController::class, 'list']); // Para dropdowns
    Route::get('/students/{student}/report', [StudentController::class, 'getStudentReport'])
            ->name('students.report');
    Route::get('/students/{student}', [StudentController::class, 'show']); // Para ver detalles de un estudiante
    Route::delete('/students/{student}', [StudentController::class, 'destroy']); // Para eliminar estudiantes
    Route::put('/students/{student}', [StudentController::class, 'update']); // Para modificar estudiantes
    // Rutas para Asignaturas (MVP + lista para selects)
    Route::get('/subjects', [SubjectController::class, 'index']);
    Route::get("/subjects/{subject}", [SubjectController::class, 'show']); // Para ver detalles de una asignatura
    Route::post('/subjects', [SubjectController::class, 'store']);
    Route::get('/subjects/list', [SubjectController::class, 'list']); // Para dropdowns
    Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy']); // Para eliminar asignaturas
    Route::put('/subjects/{subject}', [SubjectController::class, 'update']); // Para modificar asignaturas

    // Rutas para Matrículas (MVP + consulta opcional)
    Route::get('/enrollments', [EnrollmentController::class, 'index']); // Opcional
    Route::post('/enrollments', [EnrollmentController::class, 'store']);
    Route::delete('/enrollments/{enrollment}', [EnrollmentController::class, 'destroy']); // Para eliminar matrículas

    // Rutas para Calificaciones (MVP)
    Route::get('/grades', [GradeController::class, 'index']);
    Route::post('/grades', [GradeController::class, 'store']);
    Route::get('/grades/{student}', [GradeController::class, 'show']); // Para ver calificaciones de un estudiante
    Route::delete('/grades/{grade}', [GradeController::class, 'destroy']); // Para eliminar calificaciones
    Route::put('/grades/{grade}', [GradeController::class, 'update']); // Para modificar calificaciones
});
