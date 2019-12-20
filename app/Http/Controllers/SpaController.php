<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class SpaController extends Controller
{
    public function index()
    {
        if (! Auth::check()) {
            return view('splash');
        }

        return view('spa');
    }
}
