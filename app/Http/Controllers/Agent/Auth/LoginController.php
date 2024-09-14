<?php

namespace App\Http\Controllers\Agent\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use App\Traits\Agent\LoggedInUsers;
use Exception;

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

    protected $request_data;

    use AuthenticatesUsers, LoggedInUsers;

    public function showLoginForm()
    {
        $page_title = __("Agent Login");
        return view('agent.auth.login', compact(
            'page_title',
        ));
    }


    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        $this->request_data = $request;
        $request->validate([
            'credentials'   => 'required',
            'password'      => 'required|string',
        ]);
    }


    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        $request->merge(['status' => true]);
        $request->merge([$this->username() => $request->credentials]);
        return $request->only($this->username(), 'password', 'status');
    }


    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        $request = $this->request_data->all();
        $credentials = $request['credentials'];
        if (filter_var($credentials, FILTER_VALIDATE_EMAIL)) {
            return "email";
        }
        // 16/08/2024
        // return "username";
        return "email";
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            "credentials" => [trans('auth.failed')],
        ]);
    }


    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard("agent");
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
        $user->update([
            'two_factor_verified'   => false,
        ]);
        $user->createQr();
        $this->refreshUserWallets($user);
        $this->createLoginLog($user);
        return redirect()->intended(route('agent.dashboard'));
    }

    public function sanctumLogin(Request $request)
    {
        /* // Validation des informations de connexion
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Tentative d'authentification
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        } */

        // Récupération de l'utilisateur authentifié
        $user = Auth::user();

        // Création d'un token pour l'utilisateur
        $token = $user->createToken('auth_token')->plainTextToken;

        // Réponse avec le token
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
}
