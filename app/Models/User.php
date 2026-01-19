<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'printer_name',
        'print_via_bluetooth',
        'auto_print',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'print_via_bluetooth' => 'boolean',
            'auto_print' => 'boolean',
        ];
    }

    /**
     * Get effective printer name for this user
     * Falls back to global settings if user hasn't set their own
     */
    public function getEffectivePrinterName(): ?string
    {
        if (!empty($this->printer_name)) {
            return $this->printer_name;
        }

        // Fallback ke pengaturan global
        $setting = \App\Models\Setting::first();
        return $setting?->name_printer_local;
    }

    /**
     * Get effective print via bluetooth setting for this user
     * Falls back to global settings if user hasn't set their own
     */
    public function getEffectivePrintViaBluetooth(): bool
    {
        // Jika user sudah set printer_name sendiri, gunakan setting user
        if (!empty($this->printer_name)) {
            return $this->print_via_bluetooth ?? false;
        }

        // Fallback ke pengaturan global
        $setting = \App\Models\Setting::first();
        return $setting?->print_via_bluetooth ?? false;
    }

    /**
     * Get effective auto print setting for this user
     * Falls back to global settings if user hasn't set their own
     */
    public function getEffectiveAutoPrint(): bool
    {
        // Jika user sudah set printer_name sendiri, gunakan setting user
        if (!empty($this->printer_name)) {
            return $this->auto_print ?? false;
        }

        // Fallback ke pengaturan global
        $setting = \App\Models\Setting::first();
        return $setting?->auto_print ?? false;
    }
}
