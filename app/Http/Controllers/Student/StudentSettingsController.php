<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentSettingsController extends Controller
{
    /**
     * Display the student settings page.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('settings', [
            'user' => $user,
        ]);
    }
}