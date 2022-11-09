<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Remind extends Model
{
    use HasFactory;

    /**
     * リマインド完了判定
     *
     * @var array
     */
    const IS_COMPLETE = [
        'TRUE' => 1,
        'FALSE' => 0
    ];

    protected $fillable = ['reservation_id', 'is_complete', 'remind_at'];
    protected $guarded = [];
}
