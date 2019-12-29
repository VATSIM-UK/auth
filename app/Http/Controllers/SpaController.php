<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class SpaController extends Controller
{
    public function index()
    {
        return Auth::check() ? view('spa') : view('splash');
    }
}
