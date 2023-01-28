<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
  protected $fillable = [
    'username', 'password', 'type', 'firstname', 'lastname', 'gender', 'birthdate', 'address', 'contact_number', 'salary', 'status'
  ];
}
