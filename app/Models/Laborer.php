<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laborer extends Model
{
  protected $fillable = [
    'firstname', 'lastname', 'picture', 'gender', 'birthdate', 'address', 'contact_number', 'salary', 'position', 'status'
  ];
}
