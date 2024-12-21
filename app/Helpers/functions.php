<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

function is_route(string|array $routeName): bool
{
    if (is_string($routeName)) {
        if (str_contains($routeName, '*')) {
            $pattern   = str_replace('*', '([a-zA-Z0-9_\-]+)', $routeName);
            $pattern   = str_replace('.', '\.', $pattern);
            $routeName = Route::currentRouteName();

            return preg_match('/' . $pattern . '/', $routeName);
        } else {
            return Route::currentRouteName() == $routeName;
        }
    } else {
        return in_array(Route::currentRouteName(), $routeName);
    }
}

function is_authed(): bool
{
    return Auth::check();
}

function setting(string $key): string
{
    return Setting::where('key', $key)->first()?->value ?? '';
}

function getPluralForm($number, $one, $few, $many)
{
    $number    = abs($number) % 100;
    $lastDigit = $number % 10;

    if ($number > 10 && $number < 20) {
        return $many;
    }

    if ($lastDigit > 1 && $lastDigit < 5) {
        return $few;
    }

    if ($lastDigit == 1) {
        return $one;
    }

    return $many;
}

function group(): \App\Models\Group|false {
    if (!is_authed()) {
        return false;
    }

    return Auth::user()->group;
}
