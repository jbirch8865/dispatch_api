<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class note extends Model
{
    use HasFactory;
    protected $table = 'Notes';
    protected $primaryKey = 'id';
    public $timestamps = false;

}
