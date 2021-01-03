<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class shift_needs_have_skill extends Model
{
    use HasFactory;
    protected $table = 'shift_needs_have_skills';
    public $timestamps = false;

    public function skill() : \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne('App\Models\skill','Skill_ID','skill_id');
    }

    public function need() : \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne('App\Models\shift_has_need','id','need_id');
    }

}
