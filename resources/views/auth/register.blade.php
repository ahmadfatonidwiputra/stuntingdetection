<x-guest-layout>
    <div class="auth-reveal">
        <span class="inline-flex items-center rounded-full border border-orange-200 bg-orange-50 px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-orange-700">
            Setup Awal
        </span>

        <h1 class="mt-6 text-3xl font-semibold tracking-tight text-slate-950 sm:text-[2.2rem]">
            Buat superadmin pertama.
        </h1>

        <p class="mt-3 max-w-lg text-sm leading-7 text-slate-600 sm:text-base">
            Akun ini akan menjadi superadmin utama untuk mengelola dashboard dan seluruh alur pemantauan. Setelah selesai, registrasi publik otomatis ditutup.
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="auth-reveal auth-reveal-delay mt-8 space-y-5">
        @csrf

        <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50/85 px-4 py-3 transition focus-within:border-teal-400 focus-within:bg-white focus-within:shadow-[0_0_0_4px_rgba(13,148,136,0.08)]">
            <label for="name" class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">Nama</label>
            <input
                id="name"
                class="mt-2 w-full border-0 bg-transparent p-0 text-base text-slate-900 placeholder:text-slate-400 focus:ring-0"
                type="text"
                name="name"
                value="{{ old('name') }}"
                required
                autofocus
                autocomplete="name"
                placeholder="Nama superadmin"
            >
            @error('name')
                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50/85 px-4 py-3 transition focus-within:border-teal-400 focus-within:bg-white focus-within:shadow-[0_0_0_4px_rgba(13,148,136,0.08)]">
            <label for="email" class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">Email</label>
            <input
                id="email"
                class="mt-2 w-full border-0 bg-transparent p-0 text-base text-slate-900 placeholder:text-slate-400 focus:ring-0"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autocomplete="username"
                placeholder="superadmin@domain.com"
            >
            @error('email')
                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50/85 px-4 py-3 transition focus-within:border-teal-400 focus-within:bg-white focus-within:shadow-[0_0_0_4px_rgba(13,148,136,0.08)]">
            <label for="password" class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">Password</label>
            <input
                id="password"
                class="mt-2 w-full border-0 bg-transparent p-0 text-base text-slate-900 placeholder:text-slate-400 focus:ring-0"
                type="password"
                name="password"
                required
                autocomplete="new-password"
                placeholder="Buat password aman"
            >
            @error('password')
                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50/85 px-4 py-3 transition focus-within:border-teal-400 focus-within:bg-white focus-within:shadow-[0_0_0_4px_rgba(13,148,136,0.08)]">
            <label for="password_confirmation" class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">Konfirmasi password</label>
            <input
                id="password_confirmation"
                class="mt-2 w-full border-0 bg-transparent p-0 text-base text-slate-900 placeholder:text-slate-400 focus:ring-0"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="Ulangi password"
            >
            @error('password_confirmation')
                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="rounded-[1.5rem] border border-orange-200 bg-orange-50 px-4 py-3 text-sm leading-6 text-orange-800">
            Setelah akun ini dibuat, tidak akan ada registrasi publik kedua. Superadmin tambahan hanya bisa dibuat melalui pengembangan lanjutan di dalam sistem.
        </div>

        <button
            type="submit"
            class="w-full rounded-2xl bg-gradient-to-r from-slate-950 via-teal-700 to-emerald-600 px-5 py-3.5 text-sm font-semibold text-white shadow-[0_20px_45px_-20px_rgba(15,23,42,0.8)] transition hover:scale-[1.01] hover:shadow-[0_24px_50px_-18px_rgba(15,23,42,0.85)] focus:outline-none focus:ring-2 focus:ring-teal-300 focus:ring-offset-2"
        >
            Aktifkan superadmin
        </button>

        <a
            href="{{ route('login') }}"
            class="inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-2"
        >
            Kembali ke login
        </a>
    </form>
</x-guest-layout>
