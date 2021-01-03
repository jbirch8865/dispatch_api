<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class shift_has_note extends Model
{
    use HasFactory;
    protected $table = 'Shift_Has_Notes';
    public $timestamps = false;

    public function note(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne('App\Models\note', 'id', 'shift_notes');
    }

    public function shift(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\shift', 'shift_id', 'shift_id');
    }
}
