<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudyRequest extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $dates = ['confirmed_at'];

}
