<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EducationalPin extends Model
{
    protected $fillable = [
        'user_id',
        'transaction_ref',
        'exam_name',
        'quantity',
        'pins',
        'amount',
        'status'
    ];
}
