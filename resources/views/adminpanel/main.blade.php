@extends('layouts.admin')
@section('title', 'Главная')
@section('assets')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection
@section('main')
    <div class="container-fluid">
        <h1>Статистика</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                {{ Breadcrumbs::render('admin.main') }}
            </ol>
        </nav>
        <div class="container-fluid">
            <div class="row d-inline-flex justify-content-between">
                <div class="col-lg-4">
                    <div class="chart-container">
                        <canvas id="user-statistic" class="chart-statistic-large"></canvas>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="chart-container">
                        <canvas id="medicine-statistic" class="chart-statistic-large"></canvas>
                    </div>
                </div>
                <div class="col-sm-12 col-md-12 col-lg-4">
                    <canvas id="analyses-statistic" class="chart-statistic-large"></canvas>
                </div>
            </div>
        </div>
    </div>
    <script>
        const dataUsers = {
            labels: [
                'Доктора',
                'Пациенты',
                'Администрация'
            ],
            datasets: [{
                label: 'Соотношение пользователей',
                data: [{{ $doctorsCount }}, {{ $patientCount }}, {{ $adminsCount }}],
                backgroundColor: [
                    '#efefef',
                    'rgb(54, 162, 235)',
                    'orange'
                ],
                hoverOffset: 4
            }]
        }
        const configUsers = {
            type: 'doughnut',
            data: dataUsers
        }
        const dataDictionary = {
            labels: [
                'Клиники',
                'Лекарства',
                'Диагнозы'
            ],
            datasets: [{
                label: 'Соотношение словарей',
                data: [{{ $clinicsCount }}, {{ $drugsCount }}, {{ $diagnosesCount }}],
                backgroundColor: [
                    '#af7b4d',
                    'rgb(84,42,0)',
                    '#3c8517'
                ],
                hoverOffset: 4
            }]
        }
        const configDictionary = {
            type: 'doughnut',
            data: dataDictionary
        }

        const userChart = document.getElementById('user-statistic');
        const medicineChart = document.getElementById('medicine-statistic');
        const analysesChart = document.getElementById('analyses-statistic');

        new Chart(userChart, configUsers);
        new Chart(medicineChart, configDictionary);
        new Chart(analysesChart, configUsers);
    </script>
@endsection
