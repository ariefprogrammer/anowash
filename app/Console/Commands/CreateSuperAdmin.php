<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateSuperAdmin extends Command
{
    protected $signature = 'app:create-super-admin';
    protected $description = 'Create the first super_admin user for AnoWash';

    public function handle(): void
    {
        $name = $this->ask('Nama');
        $email = $this->ask('Email');
        $password = $this->secret('Password');

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $this->info("Super admin '{$email}' berhasil dibuat.");
    }
}