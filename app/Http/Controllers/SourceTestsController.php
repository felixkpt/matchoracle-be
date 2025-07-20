<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SourceTestsController extends Controller
{
    public function index(Request $request)
    {
        return view('source-tests.index');
    }
}
