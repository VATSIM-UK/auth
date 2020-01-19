<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class SpaController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            if (Request::session()->has('intended')) {
                return redirect(Request::session()->pull('intended'));
            }
            return view('spa');
        }

        if (Request::path() != "/") {
            Request::session()->put('intended', Request::path());
            return redirect('/');
        }

        return view('splash');
    }
}
