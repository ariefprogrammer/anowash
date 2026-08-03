<?php

namespace App\Filament\Resources\Marketings\Pages;

use App\Filament\Resources\Marketings\MarketingResource;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateMarketing extends CreateRecord
{
    protected static string $resource = MarketingResource::class;

    protected ?string $pendingPassword = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->pendingPassword = $data['password'] ?? null;
        unset($data['password']);

        // Kode referral digenerate otomatis oleh sistem, bukan diinput manual —
        // supaya dijamin unik dan tidak typo saat nanti diketik ulang oleh Owner.
        $data['referral_code'] = $this->generateUniqueReferralCode();

        return $data;
    }

    protected function generateUniqueReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (\App\Models\Marketing::where('referral_code', $code)->exists());

        return $code;
    }

    protected function afterCreate(): void
    {
        $marketing = $this->record;

        if (User::where('email', $marketing->email)->exists()) {
            Notification::make()
                ->title('Marketing tersimpan, tapi akun login GAGAL dibuat')
                ->body("Email {$marketing->email} sudah dipakai user lain.")
                ->danger()
                ->persistent()
                ->send();

            return;
        }

        User::create([
            'marketing_id' => $marketing->id,
            'name' => $marketing->name,
            'email' => $marketing->email,
            'phone' => $marketing->phone,
            'password' => Hash::make($this->pendingPassword),
            'role' => 'marketing',
            'is_active' => true,
        ]);

        Notification::make()
            ->title('Akun marketing berhasil dibuat')
            ->body("Kode Referral: {$marketing->referral_code}\n\nBerikan kode ini ke marketing untuk dipakai saat Owner baru didaftarkan.")
            ->success()
            ->persistent()
            ->send();
    }
}