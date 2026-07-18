@extends('layouts.test_run')

@section('title', $session->test->name ?? 'Прохождение теста')

@section('content')
    <script>
        window.__TEST_FINISHED__ = @json($isFinished ?? false);
    </script>

    <style>
        .test-run-shell {
            width: 100%;
            min-width: 0;
        }

        .test-run-doctor-toolbar {
            display: flex;
            align-items: center;
            justify-content: flex-end;

            width: min(900px, calc(100% - 32px));
            margin: 16px auto 0;
        }

        .test-run-interrupt-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;

            box-sizing: border-box;
            min-height: 40px;
            padding: 9px 14px;

            border: 1px solid #f1aeb5;
            border-radius: 10px;

            background: #ffffff;
            color: #b42318;

            font-family: inherit;
            font-size: 14px;
            font-weight: 600;
            line-height: 1.2;
            text-decoration: none;

            box-shadow: 0 3px 10px rgba(16, 24, 40, 0.06);

            transition:
                background-color 0.15s ease,
                border-color 0.15s ease,
                color 0.15s ease,
                box-shadow 0.15s ease;
        }

        .test-run-interrupt-link:hover {
            border-color: #dc3545;
            background: #fff5f5;
            color: #991b1b;
            text-decoration: none;
            box-shadow: 0 5px 14px rgba(180, 35, 24, 0.1);
        }

        .test-run-interrupt-link:focus-visible {
            outline: 3px solid rgba(220, 53, 69, 0.18);
            outline-offset: 2px;
        }

        .test-run-interrupt-link__icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            width: 20px;
            height: 20px;
            flex: 0 0 20px;

            border-radius: 50%;
            background: #fee2e2;
            color: #b42318;

            font-size: 17px;
            font-weight: 700;
            line-height: 1;
        }

        #test-run-app {
            width: 100%;
            min-width: 0;
        }

        @media (max-width: 640px) {
            .test-run-doctor-toolbar {
                width: calc(100% - 24px);
                margin-top: 12px;
            }

            .test-run-interrupt-link {
                width: 100%;
            }
        }
    </style>

    <div class="test-run-shell">
        @if(
            $session->in_progress
            && auth()->user()?->doctor
            && (int) $session->locked_by === (int) auth()->user()->doctor->id
        )
            <div class="test-run-doctor-toolbar">
                <a
                    href="{{ route('doctors.tests.final_pin.form', [
                        'session' => $session->id,
                        'mode' => 'interrupt',
                    ]) }}"
                    class="test-run-interrupt-link"
                >
                    <span
                        class="test-run-interrupt-link__icon"
                        aria-hidden="true"
                    >
                        ×
                    </span>

                    <span>
                        Прервать тест и разблокировать панель
                    </span>
                </a>
            </div>
        @endif

        <div
            id="test-run-app"
            data-payload-url="{{ route('doctors.reception.tests.payload', $session) }}"
            data-submit-url="{{ route('doctors.reception.tests.submit', $session) }}"
            data-finish-url="{{ route('doctors.reception.tests.finish', $session) }}"
        ></div>
    </div>
@endsection
