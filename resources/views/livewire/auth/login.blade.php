<div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="w-full max-w-sm bg-white p-8 rounded-lg shadow">
        <h1 class="text-xl font-semibold mb-6 text-center">Masuk ke AnoWash</h1>

        <form wire:submit="authenticate" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" wire:model="email" class="w-full border rounded px-3 py-2" autofocus>
                @error('email') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Password</label>
                <input type="password" wire:model="password" class="w-full border rounded px-3 py-2">
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" wire:model="remember">
                Ingat saya
            </label>

            <button type="submit" class="w-full bg-teal-600 text-white py-2 rounded hover:bg-teal-700">
                Masuk
            </button>
        </form>
    </div>
</div>