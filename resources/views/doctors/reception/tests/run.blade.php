@extends('layouts.test_run')
@section('title', $session->test->name ?? 'Прохождение теста')
@section('assets')
    <script>
        window.__TEST_FINISHED__ = @json($isFinished ?? false);
    </script>
@endsection
@section('content')
<div id="test-run-app"
     data-payload-url="{{ route('doctors.reception.tests.payload', $session) }}"
     data-submit-url="{{ route('doctors.reception.tests.submit', $session) }}"
     data-finish-url="{{ route('doctors.reception.tests.finish', $session) }}">
</div>
@endsection
