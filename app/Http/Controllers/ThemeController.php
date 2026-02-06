<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    private const COOKIE_NAME = 'theme';

    public function toggle(Request $request): RedirectResponse
    {
        $current = (string) $request->cookie(self::COOKIE_NAME, 'dark');
        $next = $current === 'dark' ? 'light' : 'dark';

        return back()->withCookie(cookie(self::COOKIE_NAME, $next, 60 * 24 * 365));
    }

    public function set(Request $request, string $theme): RedirectResponse
    {
        $theme = $theme === 'light' ? 'light' : 'dark';

        return back()->withCookie(cookie(self::COOKIE_NAME, $theme, 60 * 24 * 365));
    }
}
