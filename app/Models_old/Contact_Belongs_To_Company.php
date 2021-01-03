<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact_Belongs_To_Company extends Model
{
    use HasFactory;
    protected $table = 'Person_Belongs_To_Company';
//    protected $primaryKey = ['person_id','customer_id'];
    public $timestamps = false;

    public function contact() : \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne('App\Models\Contact','person_id','person_id');
    }

    public function customer() : \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne('App\Models\Customer','customer_id','customer_id');
    }

}
