<x-layouts.auth title="Forgot Password">
    <div class="card">
        <div class="card-body">
            <div class="app-brand justify-content-center">
                <a href="{{ route('login') }}" class="app-brand-link gap-2">
                    <x-ui.logo :src="asset('assets/img/funflow-logo.png')" size="56" img-class="funflow-logo-auth"
                        alt="Funflow" />
                </a>
            </div>

            <h4 class="mb-2">Forgot Password? 🔒</h4>
            <p class="mb-4">Enter your email and we'll send you instructions to reset your password</p>

            @if (session('status'))
                <div class="alert alert-success" role="alert">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="forgot-password-form" class="mb-3" action="{{ route('password.email') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email"
                        value="{{ old('email') }}" placeholder="Enter your email" autofocus required />
                </div>

                <button id="submit-btn" class="btn btn-primary d-grid w-100 mb-3" type="submit">
                    Send Reset Link
                </button>
            </form>

            <div class="text-center">
                <a href="{{ route('login') }}" class="d-flex align-items-center justify-content-center">
                    <i class="bx bx-chevron-left scaleX-n1-rtl bx-sm"></i>
                    Back to login
                </a>
            </div>
        </div>
    </div>

    @push('page-js')
    <script>
        document.getElementById('forgot-password-form').addEventListener('submit', function() {
            const btn = document.getElementById('submit-btn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Sending...';
        });
    </script>
    @endpush
</x-layouts.auth>
