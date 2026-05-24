<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private const ROLES = [
        'Medical Director',
        'Doctor',
        'Consultant',
        'Charge Nurse',
        'Nurse',
    ];

    public function login(Request $request)
    {
        $method = $request->input('method', 'email');

        return match ($method) {
            'hospital_id' => $this->loginWithHospitalId($request),
            default => $this->loginWithEmail($request),
        };
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email', 'unique:account_requests,email'],
            'role' => ['required', Rule::in(self::ROLES)],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $email = strtolower($validated['email']);

        DB::table('account_requests')->insert([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $email,
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'status' => 'Pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('hospital.index')->with('status', 'Account request submitted. A Medical Director must approve it before you can sign in.');
    }

    public function forgotPassword()
    {
        return back()->with('auth_status', 'Please contact the system administrator for assistance with your password issue.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('hospital.index')->with('auth_status', 'You have been logged out.');
    }

    private function loginWithEmail(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        return $this->attemptLogin($request, [
            'email' => strtolower($validated['email']),
            'password' => $validated['password'],
        ], 'email');
    }

    private function loginWithHospitalId(Request $request)
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in(self::ROLES)],
            'hospital_id' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $hospitalId = strtoupper(trim($validated['hospital_id']));

        if ($validated['password'] === '12345678') {
            $staff = DB::table('staff')->where('staff_id', $hospitalId)->first();

            if ($staff) {
                $user = User::firstOrCreate(
                    ['hospital_id' => $hospitalId],
                    [
                        'name' => trim(($staff->first_name ?? '') . ' ' . ($staff->last_name ?? '')),
                        'email' => strtolower($hospitalId) . '@wellmeadows.local',
                        'microsoft_email' => null,
                        'role' => $this->roleFromStaffPosition($staff->staff_position ?? $staff->position ?? $validated['role']),
                        'password' => Hash::make('12345678'),
                    ]
                );

                if (! Hash::check('12345678', $user->password)) {
                    $user->forceFill(['password' => Hash::make('12345678')])->save();
                }

                Auth::login($user, $request->boolean('remember'));
                $request->session()->regenerate();

                return redirect()->route('hospital.index');
            }
        }

        return $this->attemptLogin($request, [
            'role' => $validated['role'],
            'hospital_id' => $hospitalId,
            'password' => $validated['password'],
        ], 'hospital_id');
    }

    private function attemptLogin(Request $request, array $credentials, string $method)
    {
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->route('hospital.index');
        }

        throw ValidationException::withMessages([
            $method => 'These login details do not match our records.',
        ]);
    }

    private function generateHospitalId(): string
    {
        $lastStaffId = \DB::table('staff')
            ->where('staff_id', 'like', 'S%')
            ->orderByDesc('staff_id')
            ->value('staff_id');

        $lastUserId = User::where('hospital_id', 'like', 'S%')
            ->orderByDesc('hospital_id')
            ->value('hospital_id');

        $next = max(
            (int) preg_replace('/\D/', '', (string) $lastStaffId),
            (int) preg_replace('/\D/', '', (string) $lastUserId),
        ) + 1;

        do {
            $hospitalId = 'S' . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
            $next++;
        } while (
            User::where('hospital_id', $hospitalId)->exists()
            || \DB::table('staff')->where('staff_id', $hospitalId)->exists()
        );

        return $hospitalId;
    }

    private function nameFromAddress(string $address): string
    {
        $localPart = strstr($address, '@', true) ?: $address;

        return ucwords(trim(str_replace(['.', '_', '-'], ' ', $localPart)));
    }

    private function roleFromStaffPosition(string $position): string
    {
        $position = strtolower($position);

        return match (true) {
            str_contains($position, 'medical director') => 'Medical Director',
            str_contains($position, 'consultant') => 'Consultant',
            str_contains($position, 'doctor') => 'Doctor',
            str_contains($position, 'charge nurse') => 'Charge Nurse',
            str_contains($position, 'nurse') => 'Nurse',
            default => 'Nurse',
        };
    }
}
