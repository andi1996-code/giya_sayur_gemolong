<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'logo', 'name', 'phone', 'address', 'print_via_bluetooth', 'name_printer_local', 'auto_print',
        'receipt_footer_line1', 'receipt_footer_line2', 'receipt_footer_line3', 'receipt_footer_note',
        'show_footer_thank_you', 'customer_display_image'
    ];

    protected $casts = [
        'print_via_bluetooth' => 'boolean',
        'auto_print' => 'boolean',
        'show_footer_thank_you' => 'boolean',
    ];
}
