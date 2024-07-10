<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Set the default section to 'welcome'
        $section = $request->input('section', 'welcome');
        
        return view('dashboard', compact('section'));
    }
}
