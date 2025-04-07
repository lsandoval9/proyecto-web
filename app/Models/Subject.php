<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subject extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'code'];

    public function enrollments() {
        return $this->hasMany(Enrollment::class);
    }
    
    public function grades() {
        return $this->hasMany(Grade::class);
    }
}
