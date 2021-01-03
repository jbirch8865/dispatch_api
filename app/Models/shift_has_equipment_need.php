<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class shift_has_equipment_need extends Model
{
    use HasFactory;
    protected $table = 'shift_has_equipment_needs';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function need(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne('App\Models\shift_has_need', 'id', 'shift_need_id');
    }

    public function subtype(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne('App\Models\equipment_subtype', 'subtype_id', 'subtype_id');
    }

    public function equipment(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne('App\Models\equipment', 'equipment_id', 'equipment_id');
    }
}
