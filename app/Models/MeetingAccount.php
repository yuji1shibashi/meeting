<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingAccount extends Model
{
    use HasFactory;

    protected $fillable = ['reservation_id', 'account_id'];
    protected $guarded = [];
}
