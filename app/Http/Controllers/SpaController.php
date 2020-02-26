<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Request;

class SpaController extends Controller
{
    public function index()
    {
        if (! authenticatedOnFullGuard()) {
            if (Request::path() == '/') {
                return response()->view('splash');
            }
            session()->put('url.intended', Request::url());

            return redirect('/login');
        }

        return view('spa');
    }
}
