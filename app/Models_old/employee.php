<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class employee extends Model
{
    use HasFactory;
    protected $table = 'People';
    protected $primaryKey = 'person_id';
    public $timestamps = false;

    public function employee_has_address() : \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne('App\Models\address','id','home_address');
    }

}
