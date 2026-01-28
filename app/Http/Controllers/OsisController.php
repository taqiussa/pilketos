<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class OsisController extends Controller
{
    public function landingPage(): View
    {
        return view('osis.landing');
    }

    public function voting(): View
    {
        return view('osis.voting');
    }
}
