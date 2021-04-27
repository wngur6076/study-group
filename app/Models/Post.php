<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Exceptions\PeopleExceededException;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $dates = ["deadline"];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tags()
    {
        return $this->hasMany(Tag::class);
    }

    public function studyRequest()
    {
        return $this->belongsToMany(User::class, 'study_requests')
            ->withTimestamps();
    }

    public function numberOfPeopleCheck($studyRequestCount)
    {
        if ($studyRequestCount > $this->max_number_people) {
            throw new PeopleExceededException();
        }
    }

    public function requestSignCount()
    {
        return $this->studyRequest()->whereNotNull('confirmed_at')->count();
    }
}
