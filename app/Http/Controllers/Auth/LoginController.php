<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        // Check if credentials are valid
        $credentials = $this->credentials($request);
        $user = User::where($this->username(), $credentials[$this->username()])->first();
        
        if ($user && !password_verify($credentials['password'], $user->password)) {
            $user = null;
        }

        if ($user) {
            // Check if user status is active
            if ($user->status !== User::STATUS_ACTIVE) {
                $this->incrementLoginAttempts($request);
                
                $message = match($user->status) {
                    User::STATUS_PENDING => 'Your account is pending approval. Please contact support.',
                    User::STATUS_SUSPENDED => 'Your account has been suspended. Please contact support.',
                    default => 'Your account is not active. Please contact support.'
                };
                
                throw ValidationException::withMessages([
                    $this->username() => [$message],
                ]);
            }
            
            // If user is active, attempt login
            if (Auth::attempt($credentials, $request->filled('remember'))) {
                if ($request->hasSession()) {
                    $request->session()->regenerate();
                }

                $this->clearLoginAttempts($request);

                return $this->sendLoginResponse($request);
            }
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        // Update last login information
        $user->updateLastLogin($request->ip());
        
        // Redirect based on user role
        switch ($user->role) {
            case User::ROLE_ADMIN:
                return redirect()->intended(route('admin.dashboard'));
            case User::ROLE_PARTNER:
                return redirect()->intended(route('b2b.dashboard'));
            case User::ROLE_CUSTOMER:
                return redirect()->intended(route('customer.dashboard'));
            default:
                return redirect()->intended($this->redirectTo);
        }
    }
}
