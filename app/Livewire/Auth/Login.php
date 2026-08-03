<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest')]
class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public function authenticate(): void
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt(
            ['email' => $this->email, 'password' => $this->password, 'is_active' => true],
            $this->remember
        )) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah, atau akun tidak aktif.',
            ]);
        }

        session()->regenerate();

        $this->redirect($this->redirectPathForRole(), navigate: false);
    }

    protected function redirectPathForRole(): string
    {
        return match (Auth::user()->role) {
            'super_admin' => '/admin',
            'owner', 'admin_outlet' => '/app/dashboard',
            'marketing' => '/marketing/dashboard',
            default => '/login',
        };
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}