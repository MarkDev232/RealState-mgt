<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Inquiry;
use App\Models\Property;
use App\Models\User;
use App\Policies\AppointmentPolicy;
use App\Policies\InquiryPolicy;
use App\Policies\PropertyPolicy;
use App\Policies\UserPolicy;
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
        Property::class => PropertyPolicy::class,
        User::class => UserPolicy::class,
        Appointment::class => AppointmentPolicy::class,
        Inquiry::class => InquiryPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define additional gates
        Gate::define('access-dashboard', function (User $user) {
            return in_array($user->role, ['admin', 'agent']);
        });

        Gate::define('manage-users', function (User $user) {
            return $user->role === 'admin';
        });

        Gate::define('manage-properties', function (User $user) {
            return in_array($user->role, ['admin', 'agent']);
        });

        Gate::define('view-reports', function (User $user) {
            return in_array($user->role, ['admin', 'agent']);
        });

        Gate::define('manage-settings', function (User $user) {
            return $user->role === 'admin';
        });
    }
}
