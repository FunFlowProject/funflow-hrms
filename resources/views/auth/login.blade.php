<x-layouts.auth title="Login">
    <div class="card">
        <div class="card-body">
            <div class="app-brand justify-content-center">
                <a href="{{ route('login') }}" class="app-brand-link gap-2">
                    <x-ui.logo :src="asset('assets/img/funflow-logo.png')" size="56" img-class="funflow-logo-auth"
                        alt="Funflow" />
                </a>
            </div>

            <h4 class="mb-2">Welcome back</h4>
            <p class="mb-4">Sign in to continue to your HRMS dashboard.</p>

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

            <form class="mb-3" action="{{ route('login.attempt') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="login" class="form-label">Username or Email</label>
                    <input type="text" class="form-control" id="login" name="login"
                        value="{{ old('login', old('username')) }}" placeholder="Enter your username or email"
                        autocomplete="username" required autofocus />
                </div>

                <div class="mb-3 form-password-toggle">
                    <div class="d-flex justify-content-between">
                        <label class="form-label" for="password">Password</label>
                    </div>
                    <div class="input-group input-group-merge">
                        <input type="password" id="password" class="form-control" name="password"
                            placeholder="Enter your password" autocomplete="current-password" required />
                        <span class="input-group-text"><i class="bx bx-lock-alt"></i></span>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1" {{ old('remember') ? 'checked' : '' }} />
                        <label class="form-check-label" for="remember">Remember Me</label>
                    </div>
                </div>

                <div class="mb-3">
                    <button class="btn btn-primary d-grid w-100" type="submit">Sign in</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.auth>