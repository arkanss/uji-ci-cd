@extends('layouts.auth')

@section('content')
    <div class="w-full max-w-[400px]">
        <div class="flex flex-col items-center mb-8">
            <flux:brand href="/" logo="{{ asset('images/logo-black.png') }}"
                logo:dark="{{ asset('images/logo_white.svg') }}" {{-- name="LocalPlace" --}} class="text-2xl font-bold" />
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <div class="mb-6 text-center">
                <flux:heading size="xl" level="1">Sign In</flux:heading>
            </div>

            @if ($errors->any())
                <div class="mb-4 p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                    @foreach ($errors->all() as $error)
                        <flux:text color="red" size="sm">{{ $error }}</flux:text>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login.process') }}" class="space-y-4">
                @csrf

                <flux:field>
                    <div class="justify-between mb-2">
                        <flux:label>Email</flux:label>
                        <flux:input type="email" name="email" icon="envelope" placeholder="example@gmail.com"
                            value="{{ old('email') }}" required autofocus />
                    </div>
                </flux:field>

                <flux:field>
                    <flux:label>Password</flux:label>

                    <flux:input type="password" name="password" placeholder="Masukkan password" icon="lock-closed" viewable
                        required />
                </flux:field>

                <div class="flex items-center justify-between">
                    <flux:checkbox label="Ingat saya" name="remember" size="sm" />
                </div>

                <flux:button type="submit" variant="primary" class="w-full py-2.5">
                    Masuk
                </flux:button>
            </form>
        </div>

        <p class="mt-8 text-center text-sm text-zinc-500">
            &copy; {{ date('Y') }} LocalPlace CMS. All rights reserved.
        </p>
    </div>
@endsection
