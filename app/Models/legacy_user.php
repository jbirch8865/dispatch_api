<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use jbirch8865\AzureAuth\Http\Middleware\AzureAuth;

class legacy_user extends Model
{
    use HasFactory;
    protected $table = 'Users';
    protected $primaryKey = 'person_id';
    public $timestamps = false;

    public function scopeUser($query)
    {
        $azure = new AzureAuth;
        return $query->where('access_token',$azure->Get_User_Oid(request()));
    }
}
