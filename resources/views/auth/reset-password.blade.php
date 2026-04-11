<x-layouts.auth title="Reset Password">
    <div class="card">
        <div class="card-body">
            <div class="app-brand justify-content-center">
                <a href="{{ route('login') }}" class="app-brand-link gap-2">
                    <x-ui.logo :src="asset('assets/img/funflow-logo.png')" size="56" img-class="funflow-logo-auth"
                        alt="Funflow" />
                </a>
            </div>

            <h4 class="mb-2">Reset Password 🔒</h4>
            <p class="mb-4">Create a new password for your account</p>

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="reset-password-form" class="mb-3" action="{{ route('password.update') }}" method="POST">
                @csrf

                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email"
                        value="{{ old('email', $request->email) }}" required autofocus readonly />
                </div>

                <div class="mb-3 form-password-toggle">
                    <label class="form-label" for="password">New Password</label>
                    <div class="input-group input-group-merge">
                        <input type="password" id="password" class="form-control" name="password"
                            placeholder="Enter new password" required />
                        <span class="input-group-text"><i class="bx bx-lock-alt"></i></span>
                    </div>
                </div>

                <div class="mb-3 form-password-toggle">
                    <label class="form-label" for="password_confirmation">Confirm Password</label>
                    <div class="input-group input-group-merge">
                        <input type="password" id="password_confirmation" class="form-control" name="password_confirmation"
                            placeholder="Confirm new password" required />
                        <span class="input-group-text"><i class="bx bx-lock-alt"></i></span>
                    </div>
                </div>

                <button id="submit-btn" class="btn btn-primary d-grid w-100 mb-3" type="submit">
                    Set New Password
                </button>
            </form>
        </div>
    </div>

    @push('page-js')
    <script>
        document.getElementById('reset-password-form').addEventListener('submit', function() {
            const btn = document.getElementById('submit-btn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Saving...';
        });
    </script>
    @endpush
</x-layouts.auth>
