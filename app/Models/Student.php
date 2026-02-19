<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'grade',
        'user_id',
    ];

    /**
     * Relación: Un estudiante pertenece a un usuario.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
