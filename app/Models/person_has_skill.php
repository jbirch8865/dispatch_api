<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person_Has_Skill extends Model
{
    use HasFactory;
    protected $table = 'Person_Has_Skills';
    public $timestamps = false;

    public function skill() : \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne('App\Models\skill','Skill_ID','Skill_ID');
    }

    public function employee() : \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne('App\Models\employee','person_id','Person_ID');
    }

}
