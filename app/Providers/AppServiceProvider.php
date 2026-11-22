<?php

namespace App\Providers;

use App\Models\RegistryRecord;
use App\Observers\RegistryRecordObserver;

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Private patient-only channel; a user may NEVER subscribe to another patient's counter.
        if (config('broadcasting.connections.reverb.key')) {
            \Illuminate\Support\Facades\Broadcast::connection('reverb')->channel(
                'patient.notifications.{userId}',
                static function ($user, $userId): bool {
                    return $user instanceof \App\Models\User
                        && $user->hasRole('patients')
                        && (string) $user->getAuthIdentifier() === (string) $userId;
                },
                ['guards' => ['web']]
            );
        }

        // Standard database notifications fire NotificationSent.
        // Direct SQL inserts are covered separately by PatientPortalEventNotifier.
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Notifications\Events\NotificationSent::class,
            static function (\Illuminate\Notifications\Events\NotificationSent $event): void {
                if ($event->channel !== 'database'
                    || !$event->notification instanceof \App\Notifications\PatientPortalNotification) {
                    return;
                }
                $user = $event->notifiable;
                if (!$user instanceof \App\Models\User || !$user->hasRole('patients')) {
                    return;
                }
                app(\App\Services\PatientNotificationRealtimePublisher::class)
                    ->publishAfterCommit((int) $user->getKey());
            }
        );

        \App\Models\RegistryRecord::observe(\App\Observers\PatientPortalRegistryRecordObserver::class);
        \App\Models\MedicalEncounter::observe(\App\Observers\PatientPortalMedicalEncounterObserver::class);

        \Illuminate\Support\Facades\View::composer('layouts.patient', static function ($view): void {
            $user = \Illuminate\Support\Facades\Auth::user();
            $count = $user && $user->hasRole('patients')
                && \Illuminate\Support\Facades\Schema::hasTable('notifications')
                    ? $user->unreadNotifications()->count()
                    : 0;
            $view->with('patientNotificationUnreadCount', $count);
        });
        foreach ([
            \App\Models\LabResearch::class,
            \App\Models\LabResearchResult::class,
            \App\Models\InstrumentalResearch::class,
            \App\Models\MedicineDocument::class,
            \App\Models\PatientMedicationAssignment::class,
            \App\Models\TestAssignment::class,
            \App\Models\TestSession::class,
        ] as $model) {
            $model::observe(\App\Observers\PatientPortalClinicalEventObserver::class);
        }

        // PATIENT_PORTAL_V1_END

        // DOCTOR_MEDICAL_CARD_PROVENANCE_V1
        foreach ([
            \App\Models\Anamnesis::class,
            \App\Models\Epicrisis::class,
            \App\Models\MedicineDocument::class,
            \App\Models\LabResearch::class,
            \App\Models\InstrumentalResearch::class,
            \App\Models\MedicalPrescription::class,
            \App\Models\PatientClinicalDocument::class,
            \App\Models\PatientCondition::class,
        ] as $clinicalModel) {
            $clinicalModel::observe(
                \App\Observers\ClinicalRecordOriginObserver::class
            );
        }

        \App\Models\Patient::observe(
            \App\Observers\PatientDiagnosisObserver::class
        );

        RegistryRecord::observe(RegistryRecordObserver::class);

        Blade::directive('recaptcha', function () {
            return '<input type="hidden" name="g-recaptcha-response">';
        });
        Paginator::useBootstrapFive();
    }
}
