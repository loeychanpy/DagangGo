<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-on-surface">Manajemen User & Staf</h2>
    </x-slot>

    <div class="p-6">
        @if(session('status'))
        <div class="bg-secondary/10 text-secondary-dark border border-secondary/30 px-4 py-3 rounded-lg mb-4 text-sm font-medium">
            {{ session('status') }}
        </div>
        @endif

        @if($errors->any())
        <div class="bg-red-50 text-red-600 border border-red-200 px-4 py-3 rounded-lg mb-4 text-sm">
            {{ $errors->first() }}
        </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- Form Tambah User -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-xl border border-outline-variant shadow-sm p-6">
                    <h3 class="font-bold text-base text-on-surface mb-4 pb-3 border-b border-outline-variant">Tambah Staf Baru</h3>

                    <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required value="{{ old('name') }}"
                                class="w-full rounded-lg border-outline-variant focus:border-primary focus:ring-primary text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" required value="{{ old('email') }}"
                                class="w-full rounded-lg border-outline-variant focus:border-primary focus:ring-primary text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1">Password <span class="text-red-500">*</span></label>
                            <input type="password" name="password" required
                                class="w-full rounded-lg border-outline-variant focus:border-primary focus:ring-primary text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1">Peran (Role)</label>
                            <select name="role" class="w-full rounded-lg border-outline-variant focus:border-primary focus:ring-primary text-sm">
                                <option value="staff">Staff (Kasir)</option>
                                <option value="owner">Owner (Admin)</option>
                            </select>
                        </div>
                        <button type="submit"
                            class="w-full bg-primary hover:bg-primary-dark text-white font-semibold text-sm py-2.5 rounded-lg transition">
                            Simpan Akun
                        </button>
                    </form>
                </div>
            </div>

            <!-- Daftar User -->
            <div class="md:col-span-2">
                <div class="bg-white rounded-xl border border-outline-variant shadow-sm">
                    <div class="p-5 border-b border-outline-variant">
                        <h3 class="font-bold text-base text-on-surface">Daftar Pengguna Sistem</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-surface-low border-b border-outline-variant">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Nama</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Email</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Role</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant">
                                @foreach($users as $user)
                                <tr class="hover:bg-surface-low transition">
                                    <td class="px-4 py-4 text-sm font-semibold text-on-surface">{{ $user->name }}</td>
                                    <td class="px-4 py-4 text-sm text-on-surface-variant">{{ $user->email }}</td>
                                    <td class="px-4 py-4">
                                        @if($user->role === 'owner')
                                            <span class="px-3 py-1 text-xs font-bold rounded-full bg-primary/10 text-primary">OWNER</span>
                                        @else
                                            <span class="px-3 py-1 text-xs font-bold rounded-full bg-surface-container text-on-surface-variant">STAFF</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        @if($user->id !== auth()->id())
                                            <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                                onsubmit="return confirm('Hapus pengguna {{ $user->name }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="bg-red-50 text-red-600 hover:bg-red-100 px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                                                    Hapus
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-on-surface-variant italic">Akun Anda</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
