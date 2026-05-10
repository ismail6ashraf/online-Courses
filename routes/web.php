<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Instructor;
use App\Http\Controllers\Student;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Instructor\CourseController;
use App\Http\Controllers\Student\StudentCourseController;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    $user = auth()->user();

    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    if ($user->role === 'instructor') {
        return redirect()->route('instructor.dashboard');
    }

    return redirect()->route('student.dashboard');
});

// ─── Admin Routes ─────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/users', [Admin\UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [Admin\UserController::class, 'create'])->name('users.create');
    Route::post('/users', [Admin\UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [Admin\UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [Admin\UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [Admin\UserController::class, 'destroy'])->name('users.destroy');
    Route::patch('/users/{user}/toggle-active', [Admin\UserController::class, 'toggleActive'])->name('users.toggle-active');

    Route::get('/sessions', [Admin\SessionController::class, 'index'])->name('sessions.index');
    Route::get('/sessions/{session}', [Admin\SessionController::class, 'show'])->name('sessions.show');
    Route::delete('/sessions/{session}', [Admin\SessionController::class, 'destroy'])->name('sessions.destroy');

    Route::get('/alerts', [Admin\AlertController::class, 'index'])->name('alerts.index');
    Route::get('/alerts/{alert}', [Admin\AlertController::class, 'show'])->name('alerts.show');
    Route::post('/alerts/mark-all-read', [Admin\AlertController::class, 'markAllRead'])->name('alerts.mark-all-read');
    Route::delete('/alerts/{alert}', [Admin\AlertController::class, 'destroy'])->name('alerts.destroy');

    Route::get('/reports', [Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/generate', [Admin\ReportController::class, 'generateForm'])->name('reports.generate');
    Route::post('/reports/generate', [Admin\ReportController::class, 'generate'])->name('reports.generate.store');
    Route::get('/reports/{report}', [Admin\ReportController::class, 'show'])->name('reports.show');
});

// ─── Instructor Routes ────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:instructor'])->prefix('instructor')->name('instructor.')->group(function () {
    Route::get('/dashboard', [Instructor\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/sessions', [Instructor\SessionController::class, 'index'])->name('sessions.index');
    Route::get('/sessions/create', [Instructor\SessionController::class, 'create'])->name('sessions.create');
    Route::post('/sessions', [Instructor\SessionController::class, 'store'])->name('sessions.store');
    Route::get('/sessions/{session}', [Instructor\SessionController::class, 'show'])->name('sessions.show');
    Route::get('/sessions/{session}/edit', [Instructor\SessionController::class, 'edit'])->name('sessions.edit');
    Route::put('/sessions/{session}', [Instructor\SessionController::class, 'update'])->name('sessions.update');
    Route::post('/sessions/{session}/start', [Instructor\SessionController::class, 'start'])->name('sessions.start');
    Route::post('/sessions/{session}/end', [Instructor\SessionController::class, 'end'])->name('sessions.end');
    Route::get('/sessions/{session}/live', [Instructor\SessionController::class, 'live'])->name('sessions.live');

    Route::get('/assessments', [Instructor\AssessmentController::class, 'index'])->name('assessments.index');
    Route::get('/assessments/create', [Instructor\AssessmentController::class, 'create'])->name('assessments.create');
    Route::post('/assessments', [Instructor\AssessmentController::class, 'store'])->name('assessments.store');
    Route::get('/assessments/{assessment}', [Instructor\AssessmentController::class, 'show'])->name('assessments.show');
    Route::post('/assessments/{assessment}/respond', [Instructor\AssessmentController::class, 'submitResponse'])->name('assessments.respond');

    Route::get('/tasks', [Instructor\TaskController::class, 'index'])->name('tasks.index');
    Route::patch('/tasks/{task}/status', [Instructor\TaskController::class, 'updateStatus'])->name('tasks.status');
    Route::resource('courses', CourseController::class);
});

// ─── Student Routes ───────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:student'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {

        Route::get(
            '/dashboard',
            [Student\DashboardController::class, 'index']
        )
            ->name('dashboard');

        Route::post(
            '/courses/{course}/enroll',
            [StudentCourseController::class, 'enroll']
        )
            ->name('courses.enroll');

        Route::get('/courses/{course}', [StudentCourseController::class, 'show'])
            ->name('courses.show');
    });
