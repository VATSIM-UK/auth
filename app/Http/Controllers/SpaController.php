<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class SpaController extends Controller
{

    public function index()
    {
        if (! Auth::guard('web')->check()) {
            if (Request::path() == "/") {
                return response()->view('splash');
            }
//            Request::session()->put('intended', Request::path());
            return redirect('/');
        }


//        if (Request::session()->has('intended')) {
//            return redirect(Request::session()->pull('intended'));
//        }

        return view('spa');
    }
}
