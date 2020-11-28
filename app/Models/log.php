<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class log extends Model
{
    use HasFactory;
    protected $table = 'Log';
    protected $primaryKey = 'log_id';
    protected $fillable = ['timestamp','person_id','log_entry','log_type'];
    public $timestamps = false;

    public function belongs_to_user() : \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne('App\Models\legacy_user','person_id','person_id');
    }
}
