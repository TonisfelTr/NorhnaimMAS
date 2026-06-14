<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Patient;
use App\Models\Test;
use App\Models\TestAssignment;
use App\Models\TestSession;
use App\Policies\PatientPolicy;
use App\Policies\TestAssignmentPolicy;
use App\Policies\TestPolicy;
use App\Policies\TestSessionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Test::class        => TestPolicy::class,
        TestSession::class => TestSessionPolicy::class,
        Patient::class     => PatientPolicy::class,
        TestAssignment::class => TestAssignmentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        if (config('app.debug')) {
            Gate::before(function ($user, $ability) {
                return $user->hasRole('admins') ? true : null;
            });
        }
    }
}
