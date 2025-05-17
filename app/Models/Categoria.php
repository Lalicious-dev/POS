<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Categoria extends Model
{
    use HasFactory;
    
    public function productos(){
        return $this->belongsToMany(Producto::class)->withTimestamps();
    }

    public function caracteristica(){
        return $this->belongsTo(Caracteristica::class);
    }
    
    protected $fillable = ['caracteristicas_id'];
}
