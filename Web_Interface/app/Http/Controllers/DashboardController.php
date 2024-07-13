<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index($section = 'welcome')
    {
        return view('dashboard', compact('section'));
    }
}
