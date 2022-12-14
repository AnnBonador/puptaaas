<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Event extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    protected $fillable = [
        'title', 'location', 'description', 'options', 'start', 'end'
    ];

    public function form()
    {
        return $this->hasOne(Form::class, 'form_id', 'id');
    }
}
