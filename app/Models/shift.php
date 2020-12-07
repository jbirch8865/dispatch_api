<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class shift extends Model
{
    use HasFactory;
    protected $table = 'Shift';
    protected $primaryKey = 'shift_id';
    public $timestamps = false;

    public function shift_has_needs() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\shift_has_need','shift_id');
    }

    public function shift_has_address() : \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne('App\Models\address','id','meet_address');
    }
    public function shift_has_contractor() : \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne('App\Models\Contact','person_id','contractor_id');
    }
}