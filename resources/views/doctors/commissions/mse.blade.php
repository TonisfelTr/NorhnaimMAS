@extends('doctors.prescriptions')

@section('title', 'МСЭ — форма 088/у')

@section('sub-main')
    @include('doctors.commissions.partials.page', [
        'commissionType' => \App\Models\CommissionDocument::TYPE_MSE,
    ])
@endsection
