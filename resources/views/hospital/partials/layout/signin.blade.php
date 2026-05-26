    <section class="signin" id="signin">
        <div class="signin-hero">
            <div class="brand brand-light">
                <span class="brand-mark">+</span>
                <span><strong>Wellmeadows Hospital</strong><small>Edinburgh Healthcare System</small></span>
            </div>
            <div>
                <h1>Welcome to Wellmeadows Hospital</h1>
                <p>Organized records for patients, staff, wards, appointments, and medications.</p>
                <p>The system supports role-based access for hospital modules.</p>
            </div>
            <small>&copy; 2026 Wellmeadows Hospital. All rights reserved.</small>
        </div>
        <div class="signin-panel">
            <span class="signin-icon">+</span>
            <h2>Sign In</h2>
            <p>Choose a login method or create a new account.</p>
            <div class="auth-tabs">
                <button type="button" class="active" data-auth-tab="email">Email</button>
                <button type="button" data-auth-tab="hospital_id">Hospital ID</button>
                <button type="button" data-auth-tab="register">Create Account</button>
            </div>

            @if (session('status') || session('auth_status'))
                <div class="notice">{{ session('status') ?? session('auth_status') }}</div>
            @endif
            @if ($errors->any())
                <div class="notice error">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form data-auth-form="email" class="auth-form active" method="POST" action="{{ route('login') }}">
                @csrf
                <input type="hidden" name="method" value="email">
                <label>Email Address<input type="email" name="email" value="{{ old('email') }}" required></label>
                <label>Password<input type="password" name="password" required></label>
                <div class="form-row">
                    <label class="check"><input type="checkbox" name="remember"> Remember me</label>
                    <button class="primary" type="submit">Sign In</button>
                </div>
                <button class="link" type="submit" form="forgot-password-form">Forgot password?</button>
            </form>

            <form data-auth-form="hospital_id" class="auth-form" method="POST" action="{{ route('login') }}">
                @csrf
                <input type="hidden" name="method" value="hospital_id">
                <label>Role<select name="role" required>
                    <option value="">Select role</option>
                    <option value="Medical Director" {{ old('role') === 'Medical Director' ? 'selected' : '' }}>Medical Director</option>
                    <option value="Personnel Officer" {{ old('role') === 'Personnel Officer' ? 'selected' : '' }}>Personnel Officer</option>
                    <option value="Consultant" {{ old('role') === 'Consultant' ? 'selected' : '' }}>Consultant</option>
                    <option value="Charge Nurse" {{ old('role') === 'Charge Nurse' ? 'selected' : '' }}>Charge Nurse</option>
                </select></label>
                <label>Hospital ID<input type="text" name="hospital_id" value="{{ old('hospital_id') }}" required></label>
                <label>Password<input type="password" name="password" required></label>
                <div class="form-row">
                    <button class="primary" type="submit">Sign In with Hospital ID</button>
                </div>
                <button class="link" type="submit" form="forgot-password-form">Forgot password?</button>
            </form>

            <form data-auth-form="register" class="auth-form" method="POST" action="{{ route('register') }}">
                @csrf
                <label>First Name<input type="text" name="first_name" value="{{ old('first_name') }}" required></label>
                <label>Last Name<input type="text" name="last_name" value="{{ old('last_name') }}" required></label>
                <label>Email Address<input type="email" name="email" value="{{ old('email') }}" required></label>
                <label>Role<select name="role" required>
                    <option value="">Select role</option>
                    <option value="Medical Director" {{ old('role') === 'Medical Director' ? 'selected' : '' }}>Medical Director</option>
                    <option value="Personnel Officer" {{ old('role') === 'Personnel Officer' ? 'selected' : '' }}>Personnel Officer</option>
                    <option value="Consultant" {{ old('role') === 'Consultant' ? 'selected' : '' }}>Consultant</option>
                    <option value="Charge Nurse" {{ old('role') === 'Charge Nurse' ? 'selected' : '' }}>Charge Nurse</option>
                </select></label>
                <label>Password<input type="password" name="password" required></label>
                <label>Confirm Password<input type="password" name="password_confirmation" required></label>
                <div class="form-row">
                    <button class="primary" type="submit">Request Account</button>
                </div>
                <small>A Medical Director must approve your request before your Hospital ID is active.</small>
            </form>

            <form id="forgot-password-form" method="POST" action="{{ route('password.forgot') }}" class="hidden">
                @csrf
            </form>
        </div>
    </section>
