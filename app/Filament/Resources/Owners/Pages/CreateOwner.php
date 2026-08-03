<?php

namespace App\Filament\Resources\Owners\Pages;

use App\Filament\Resources\Owners\OwnerResource;
use App\Models\Marketing;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateOwner extends CreateRecord
{
    protected static string $resource = OwnerResource::class;

    protected ?string $pendingPassword = null;
    protected ?string $pendingReferralCode = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->pendingPassword = $data['password'] ?? null;
        unset($data['password']);

        $this->pendingReferralCode = $data['referral_code_input'] ?? null;
        unset($data['referral_code_input']);

        if ($this->pendingReferralCode) {
            $marketing = Marketing::where('referral_code', strtoupper(trim($this->pendingReferralCode)))
                ->where('is_active', true)
                ->first();

            $data['referred_by_marketing_id'] = $marketing?->id;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $owner = $this->record;

        if ($this->pendingReferralCode && ! $owner->referred_by_marketing_id) {
            Notification::make()
                ->title('Owner tersimpan, tapi kode referral tidak valid')
                ->body("Kode '{$this->pendingReferralCode}' tidak ditemukan atau marketing tidak aktif. Owner tidak tertaut ke marketing manapun — bisa diperbaiki manual lewat database jika perlu.")
                ->warning()
                ->persistent()
                ->send();
        }

        if (User::where('email', $owner->email)->exists()) {
            Notification::make()
                ->title('Owner tersimpan, tapi akun login GAGAL dibuat')
                ->body("Email {$owner->email} sudah dipakai user lain. Buat akun login secara manual lewat halaman Users.")
                ->danger()
                ->persistent()
                ->send();

            return;
        }

        User::create([
            'owner_id' => $owner->id,
            'outlet_id' => null,
            'name' => $owner->owner_name,
            'email' => $owner->email,
            'phone' => $owner->phone,
            'password' => Hash::make($this->pendingPassword),
            'role' => 'owner',
            'is_active' => true,
        ]);

        Notification::make()
            ->title('Akun login Owner berhasil dibuat')
            ->body("Email: {$owner->email}\nSilakan sampaikan password yang tadi kamu input ke pemilik usaha.")
            ->success()
            ->send();
    }
}