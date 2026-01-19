<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Report extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'report_type', 'start_date', 'start_time', 'end_date', 'end_time', 'path_file'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];
}

