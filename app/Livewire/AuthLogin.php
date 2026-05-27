<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthLogin extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;

    public function login()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            session()->regenerate();

            $user = Auth::user();
            if ($user->role === 'admin' && is_null($user->tenant_id)) {
                $defaultUrl = '/super-admin';
            } elseif ($user->hasRole('admin')) {
                $defaultUrl = '/admin';
            } else {
                $defaultUrl = '/vendedor';
            }
            
            $url = session()->pull('url.intended', $defaultUrl);
            $this->dispatch('login-success', url: url($url));
            return;
        }

        throw ValidationException::withMessages([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ]);
    }

    public function render()
    {
        return view('livewire.auth-login')->layout('components.layouts.app', ['title' => 'ASEL POS - Login']);
    }
}
