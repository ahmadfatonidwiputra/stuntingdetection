<?php

namespace App\Http\Controllers;

class LandingController extends Controller
{
    public function home()
    {
        return view('landing.home');
    }

    public function tentangStunting()
    {
        return view('landing.tentang-stunting');
    }

    public function layanan()
    {
        return view('landing.layanan');
    }
}
