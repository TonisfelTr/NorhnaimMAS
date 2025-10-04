@extends('layouts.doctor')
@section('main')
    <nav class="navbar navbar-expand-lg bg-body-hospital">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link @if(is_route('doctors.reception.create')) active @endif" href="{{ route('doctors.reception.create') }}">Создать запись</a></li>
                    <li class="nav-item"><a class="nav-link @if(is_route('doctors.reception')) active @endif" href="{{ route('doctors.reception') }}">Текущая регистратура</a></li>
                    <li class="nav-item"><a class="nav-link @if(is_route('doctors.reception.archives')) active @endif" href="{{ route('doctors.reception.archives') }}">Архивы</a></li>
                </ul>
            </div>
            <form class="d-flex" role="search">
                <input class="form-control me-2" type="search" placeholder="Поиск в регистратуре" aria-label="Поиск в регистратуре">
                <button class="btn btn-outline-success" type="submit">Поиск</button>
            </form>
        </div>
    </nav>
    @yield('sub-main')
@endsection
