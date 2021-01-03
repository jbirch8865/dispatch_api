<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class shift_has_need extends Model
{
    use HasFactory;
    protected $table = 'shift_has_needs';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function has_person(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne('App\Models\employee', 'person_id', 'people_id');
    }

    public function has_skills(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\shift_needs_have_skill', 'need_id', 'id');
    }
}
