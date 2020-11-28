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

    public function has_skills(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\person_has_skill', 'Person_ID', 'person_id');
    }

    public function has_sent_sms(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\sms', 'from_number', 'phone_number');
    }

    public function has_received_sms(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\sms', 'to_number', 'phone_number');
    }
}
