<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

function is_route(string|array $routeName): bool {
    if (is_string($routeName)) {
        if (str_contains($routeName, '*')) {
            $pattern = str_replace('*', '([a-zA-Z0-9_\-]+)', $routeName);
            $pattern = str_replace('.', '\.', $pattern);
            $routeName = Route::currentRouteName();

            return preg_match('/' . $pattern . '/', $routeName);
        } else {
            return Route::currentRouteName() == $routeName;
        }
    } else {
        return in_array(Route::currentRouteName(), $routeName);
    }
}

function is_authed(): bool {
    return Auth::check();
}

function setting(string $key): string {
    return Setting::where('key', $key)->first()?->value ?? '';
}
