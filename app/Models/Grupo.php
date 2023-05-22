<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    use HasFactory;

    protected $table = 'sistsic_grupos';

    /**
     * Override fillable property data.
     *
     * @var array
     */
    protected $fillable = [
        'descricao',
        'status'
    ];
}
