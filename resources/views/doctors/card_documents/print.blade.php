@extends('doctors.card_documents.print_layout')
@section('print_title', $config['title'] . ' ' . $document->number)
@section('print_body')
    @include('doctors.card_documents.partials.document')
@endsection
