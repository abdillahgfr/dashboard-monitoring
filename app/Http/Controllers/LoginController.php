<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class LoginController extends Controller
{
    public function showForm()
    {
        return view('auth.login');
    }
    
}
