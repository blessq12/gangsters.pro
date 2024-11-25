<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionIdentifier extends Model
{
    use HasFactory;

    protected $fillable = ['session_id', 'user_id', 'is_temporary'];
}
