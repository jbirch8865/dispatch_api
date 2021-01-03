<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;
    protected $table = 'People';
    protected $primaryKey = 'person_id';
    public $timestamps = false;

    public function belongs_to_company() : \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne('App\Models\Contact_Belongs_To_Company','person_id');
    }
}
