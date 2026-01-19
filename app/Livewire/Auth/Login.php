<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;

class Login extends Component
{
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required|min:6')]
    public string $password = '';

    public bool $remember = false;
    public ?string $error = null;

    public function login()
    {
        $this->validate();

        if (Auth::attempt(
            ['email' => $this->email, 'password' => $this->password],
            $this->remember
        )) {
            session()->regenerate();
            return redirect()->intended('/');
        }

        $this->error = 'Email atau password tidak sesuai.';
        $this->password = '';
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
