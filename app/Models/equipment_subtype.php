<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class equipment_subtype extends Model
{
    use HasFactory;
    protected $table = 'Equipment_Subtypes';
    protected $primaryKey = 'subtype_id';
    public $timestamps = false;

    public function equipment(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\equipment', 'equipment_subtype', 'subtype_id');
    }

}
