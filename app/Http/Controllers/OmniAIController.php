<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OmniAIController extends Controller
{
    /**
     * Hiển thị trang Chat OmniAI.
     */
    public function index()
    {
        return view('vendor.omni_ai');
    }
}


