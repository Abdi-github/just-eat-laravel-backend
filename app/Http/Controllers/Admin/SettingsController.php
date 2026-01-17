<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Settings/Index');
    }

    public function updateLanguage(Request $request): RedirectResponse
    {
        $request->validate([
            'preferred_language' => ['required', 'in:fr,de,en'],
        ]);

        $request->session()->put('admin_locale', $request->preferred_language);

        return back()->with('success', 'Settings saved.');
    }
}
