<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DebugController extends Controller
{
    /**
     * Hiển thị trang debug LLM
     */
    public function llmTest()
    {
        return view('debug.llm_test');
    }
}
