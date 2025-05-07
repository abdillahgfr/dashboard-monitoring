<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LoginController extends Controller
{
    public function showForm()
    {
        return view('auth.login');
    }

    public function loginApi(Request $request)
    {
        $response = Http::withOptions(['verify' => false])
        ->asForm()
        ->post('https://jakaset.jakarta.go.id/reklame-api/st/api/auth/login-cms', [
            'username' => $request->input('username'),
            'password' => $request->input('password'),
        ]);

        // $data = $response->json();
        $result = json_decode($response, true);

        // Check if login is successful and token exists
        if (isset($result['success']) && $result['success'] === true && isset($result['data']['api_token'])) {
            // Save user info and token in session
            session([
                'api_token' => $result['data']['api_token'],
                'user' => $result['data']['user'],
                'roles' => $result['data']['data_mapping_roles'],
                'permissions' => $result['data']['permissions'],
            ]);

            // Redirect to dashboard or other protected route
            return redirect()->route('home')->with('success', 'Login successful!');
        }

        return back()->withErrors(['login_error' => 'Username atau Password tidak sesuai.']);
    }

    public function logout()
    {
        // Clear all session data
        session()->flush();

        // Redirect to the login page
        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }


    
}
