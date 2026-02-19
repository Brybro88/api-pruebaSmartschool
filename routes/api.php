<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;

// ─── Public Routes (no auth) ─────────────────────────────────

// POST - Login (Sanctum Token)
Route::post('/login', function (Request $request) {

    $request->validate([
        'email'    => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['error' => 'Credenciales incorrectas'], 401);
    }

    $user->tokens()->delete();
    $token = $user->createToken('smartschool-token')->plainTextToken;

    return response()->json([
        'token' => $token,
        'user'  => [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->role ?? 'parent',
        ],
    ]);
});

// POST - Register (Create account + auto-login)
Route::post('/register', function (Request $request) {

    $request->validate([
        'name'     => 'required|string|max:255',
        'email'    => 'required|email|unique:users,email',
        'password' => 'required|min:6',
    ]);

    $user = User::create([
        'name'     => $request->name,
        'email'    => $request->email,
        'password' => Hash::make($request->password),
    ]);

    $token = $user->createToken('smartschool-token')->plainTextToken;

    return response()->json([
        'token' => $token,
        'user'  => [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->role ?? 'parent',
        ],
    ], 201);
});

// POST - Reset Password (Prototype: direct reset, no email verification)
Route::post('/reset-password', function (Request $request) {

    $request->validate([
        'email'    => 'required|email',
        'password' => 'required|min:6',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json(['error' => 'No se encontró una cuenta con ese email.'], 404);
    }

    $user->update(['password' => Hash::make($request->password)]);
    $user->tokens()->delete(); // Revoke all old tokens for security

    return response()->json(['mensaje' => 'Contraseña actualizada con éxito. Ya puedes iniciar sesión.']);
});

// ─── Protected Routes (auth:sanctum) ─────────────────────────

Route::middleware('auth:sanctum')->group(function () {

    // POST - Logout (Revoke Token)
    Route::post('/logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['mensaje' => 'Sesión cerrada con éxito']);
    });

    // GET - Listar alumnos (admin = ALL with parent info, parent = own)
    Route::get('/alumnos', function (Request $request) {
        $user = $request->user();
        $isAdmin = ($user->role ?? 'parent') === 'admin' || $user->id === 1;

        if ($isAdmin) {
            // Admin sees ALL students + parent name
            $students = DB::table('students')
                ->leftJoin('users', 'students.user_id', '=', 'users.id')
                ->select('students.*', 'users.name as parent_name')
                ->get();
        } else {
            // Parent sees only their own
            $students = DB::table('students')
                ->where('user_id', $user->id)
                ->get();
        }

        return response()->json($students);
    });

    // POST - Guardar un nuevo alumno (vinculado al usuario logueado)
    Route::post('/alumnos', function (Request $request) {

        $request->validate([
            'name'  => 'required',
            'grade' => 'required'
        ]);

        DB::table('students')->insert([
            'name'       => $request->name,
            'grade'      => $request->grade,
            'user_id'    => $request->user()->id,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['mensaje' => 'Alumno guardado con éxito'], 201);
    });

    // POST - Registrar asistencia (Check-In)
    Route::post('/attendances', function (Request $request) {

        $request->validate([
            'student_id' => 'required|exists:students,id'
        ]);

        // Verify the student belongs to the logged-in user (admin can access any)
        $user = $request->user();
        $isAdmin = ($user->role ?? 'parent') === 'admin' || $user->id === 1;

        $query = DB::table('students')->where('id', $request->student_id);
        if (!$isAdmin) {
            $query->where('user_id', $user->id);
        }
        $student = $query->first();

        if (!$student) {
            return response()->json(['error' => 'Estudiante no encontrado'], 404);
        }

        $yaExiste = DB::table('attendances')
            ->where('student_id', $request->student_id)
            ->whereDate('check_in', now()->toDateString())
            ->exists();

        if ($yaExiste) {
            return response()->json(['error' => 'Attendance already recorded today'], 409);
        }

        DB::table('attendances')->insert([
            'student_id' => $request->student_id,
            'check_in'   => now(),
            'status'     => 'present',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['mensaje' => 'Asistencia registrada con éxito'], 201);
    });

    // GET - Historial de asistencias de un alumno
    Route::get('/students/{id}/attendances', function (Request $request, $id) {

        // Verify ownership (admin can access any)
        $user = $request->user();
        $isAdmin = ($user->role ?? 'parent') === 'admin' || $user->id === 1;

        $query = DB::table('students')->where('id', $id);
        if (!$isAdmin) {
            $query->where('user_id', $user->id);
        }
        $student = $query->first();

        if (!$student) {
            return response()->json(['error' => 'Estudiante no encontrado'], 404);
        }

        $attendances = DB::table('attendances')
            ->where('student_id', $id)
            ->orderBy('check_in', 'desc')
            ->get();

        return response()->json([
            'student'     => $student,
            'attendances' => $attendances
        ]);
    });

    // POST - Subir foto de alumno (Digital ID)
    Route::post('/students/{id}/photo', function (Request $request, $id) {

        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:5120'
        ]);

        // Verify ownership (admin can access any)
        $user = $request->user();
        $isAdmin = ($user->role ?? 'parent') === 'admin' || $user->id === 1;

        $query = DB::table('students')->where('id', $id);
        if (!$isAdmin) {
            $query->where('user_id', $user->id);
        }
        $student = $query->first();

        if (!$student) {
            return response()->json(['error' => 'Estudiante no encontrado'], 404);
        }

        $file = $request->file('photo');
        $fileName = 'student_' . $id . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('students'), $fileName);

        $photoUrl = url('students/' . $fileName);

        DB::table('students')->where('id', $id)->update([
            'photo_url'  => $photoUrl,
            'updated_at' => now()
        ]);

        return response()->json([
            'mensaje'   => 'Foto subida con éxito',
            'photo_url' => $photoUrl
        ], 201);
    });

    // GET - Dashboard Stats (admin = ALL, parent = own students)
    Route::get('/dashboard-stats', function (Request $request) {

        $user = $request->user();
        $isAdmin = ($user->role ?? 'parent') === 'admin' || $user->id === 1;

        $query = DB::table('students');
        if (!$isAdmin) {
            $query->where('user_id', $request->user()->id);
        }
        $studentIds = $query->pluck('id');

        $totalStudents = $studentIds->count();

        $attendanceToday = DB::table('attendances')
            ->whereIn('student_id', $studentIds)
            ->whereDate('check_in', now()->toDateString())
            ->count();

        $absentToday = $totalStudents - $attendanceToday;

        $attendanceRate = $totalStudents > 0
            ? round(($attendanceToday / $totalStudents) * 100, 1)
            : 0;

        return response()->json([
            'total_students'   => $totalStudents,
            'attendance_today' => $attendanceToday,
            'absent_today'     => $absentToday,
            'attendance_rate'  => $attendanceRate,
        ]);
    });
}); // end auth:sanctum group
