<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Importar los modelos necesarios
        $userModel = \App\Models\User::class;
        $studentModel = \App\Models\Student::class;

        // Asegurarse de tener al menos un usuario
        $user = $userModel::first();

        if (!$user) {
            // Crear un usuario de prueba si no existe
            $user = $userModel::factory()->create([
                'name' => 'Usuario de Prueba',
                'email' => 'test@example.com',
            ]);
        }

        // Crear estudiantes de prueba
        $estudiantes = [
            ['name' => 'Leo García', 'grade' => '1A'],
            ['name' => 'Ana Torres', 'grade' => '2B'],
            ['name' => 'Carlos Ruiz', 'grade' => '3C'],
            ['name' => 'Sofia Méndez', 'grade' => '1A'],
            ['name' => 'María López', 'grade' => '2A'],
            ['name' => 'Juan Pérez', 'grade' => '3B'],
        ];

        foreach ($estudiantes as $estudiante) {
            $studentModel::create([
                'name' => $estudiante['name'],
                'grade' => $estudiante['grade'],
                'user_id' => $user->id,
            ]);
        }

        echo "✅ Se crearon " . count($estudiantes) . " estudiantes de prueba\n";
    }
}
