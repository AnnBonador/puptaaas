<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    use HasFactory;
    protected $fillable = ['award_form', 'photocard', 'requirements'];

    public function requirement()
    {
        return $this->hasMany(FormReq::class, 'form_id', 'id');
    }
}
