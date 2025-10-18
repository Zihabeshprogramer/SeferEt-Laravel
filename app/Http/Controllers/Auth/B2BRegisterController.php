<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;

class B2BRegisterController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Show the B2B registration form.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        return view('auth.b2b.register', [
            'userTypes' => [
                User::ROLE_PARTNER => 'Travel Package Partner',
                User::ROLE_HOTEL_PROVIDER => 'Hotel Service Provider',
                User::ROLE_TRANSPORT_PROVIDER => 'Transport Service Provider',
            ]
        ]);
    }

    /**
     * Handle a B2B registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        // Don't log the user in automatically - redirect to pending approval page
        return redirect()->route('b2b.pending')->with('success',
            'Your registration has been submitted successfully! Your account is pending admin approval. You will be notified once approved.'
        );
    }

    /**
     * Get a validator instance for a B2B registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'user_type' => ['required', 'string', 'in:' . implode(',', [User::ROLE_PARTNER, User::ROLE_HOTEL_PROVIDER, User::ROLE_TRANSPORT_PROVIDER])],
            'company_name' => ['required', 'string', 'max:255'],
            'company_registration_number' => ['required', 'string', 'max:100', 'unique:users,company_registration_number'],
            'phone' => ['required', 'string', 'max:20'],
            'service_type' => ['required_if:user_type,' . User::ROLE_HOTEL_PROVIDER . ',' . User::ROLE_TRANSPORT_PROVIDER, 'string', 'nullable'],
            'service_categories' => ['array', 'nullable'],
            'coverage_areas' => ['array', 'nullable'],
            'certification_number' => ['string', 'nullable'],
        ], [
            'company_registration_number.unique' => 'This company registration number is already registered.',
            'user_type.in' => 'Please select a valid user type.',
            'service_type.required_if' => 'Service type is required for service providers.',
        ]);
    }

    /**
     * Create a new B2B partner instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['user_type'],
            'status' => User::STATUS_PENDING,
            'phone' => $data['phone'],
            'company_name' => $data['company_name'],
            'company_registration_number' => $data['company_registration_number'],
            'service_type' => $data['service_type'] ?? null,
            'service_categories' => $data['service_categories'] ?? null,
            'coverage_areas' => $data['coverage_areas'] ?? null,
            'certification_number' => $data['certification_number'] ?? null,
        ]);
    }

    /**
     * Show the B2B pending approval page.
     *
     * @return \Illuminate\View\View
     */
    public function pending()
    {
        return view('auth.b2b.pending');
    }
}
