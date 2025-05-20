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

    private $urlLogin = 'https://jakaset.jakarta.go.id/api_login/api';

    private function callUser($username, $password, $tahun = '', $safety = false)
    {
        $user = null;
        try {
            $opts = [
                'http' => [
                    'method'  => 'POST',
                    'header'  => 'Content-Type: application/x-www-form-urlencoded',
                    'content' => http_build_query([
                        'password' => $password,
                        'tahun'    => $tahun,
                        'username' => $username,
                        // 'sistem'   => "Jakaset/" . env('SERVER'),
                    ]),
                ],
            ];
            $context = stream_context_create($opts);
            $user    = @file_get_contents($this->urlLogin . '/auth', false, $context);
            if (empty($user)) {
                throw new \Exception("Gagal melakukan login", 1);
            }
            $user = json_decode($user);
            return $user;
        } catch (\Throwable $th) {
            if ($safety == false) {
                throw $th;
            }
        }
        return empty($user) ? null : $user;
    }

    public function authenticate($username, $password, $tahun = '')
    {
        $user = $this->callUser($username, $password, $tahun, true);
        if (!$user && is_numeric($tahun)) {
            $user = $this->callUser($username, $password, $tahun + 1, true);
        }
        return $user;
    }

    public function loginApi(Request $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');
        $tahun = $request->input('tahun', '');

        $user = $this->authenticate($username, $password, $tahun);

        if ($user && isset($user->success) && $user->code == 200) {
            // Store user data in session if needed
            session([
                'user' => $user->user,
            ]);

            return redirect()->route('index')->with('success', 'Login successful!');
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
