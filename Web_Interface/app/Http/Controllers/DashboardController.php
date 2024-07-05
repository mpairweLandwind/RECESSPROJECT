<?php

namespace App\Http\Controllers;



class DashboardController extends Controller
{
    public function index($section = 'welcome')
    {

        return view('dashboard', compact('section'));
    }
    //
}
