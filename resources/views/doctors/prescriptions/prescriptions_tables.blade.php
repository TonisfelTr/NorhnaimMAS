@extends('doctors.prescriptions')
@section('title', 'Выписка рецептов')
@section('assets')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.15.2/js/standalone/selectize.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.15.2/css/selectize.bootstrap5.min.css">
@endsection
@section('sub-main')
    <div class="p-4">
        <h1>Рецептурный отпуск</h1>
        <hr>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <h3>Выписано Вами на сегодня</h3>
                    <table class="styled-table">
                        <tbody>
                            <tr>
                                <td>Выписано антипсихотиков</td>
                                <td>{{ $statistics[1] }}</td>
                            </tr>
                            <tr>
                                <td>Выписано антидепрессантов</td>
                                <td>{{ $statistics[2] }}</td>
                            </tr>
                            <tr>
                                <td>Выписано анксиолитиков</td>
                                <td>{{ $statistics[7] }}</td>
                            </tr>
                            <tr>
                                <td>Выписано нормотимиков</td>
                                <td>{{ $statistics[3] }}</td>
                            </tr>
                            <tr>
                                <td>Выписано снотворных</td>
                                <td>{{ $statistics[9] }}</td>
                            </tr>
                            <tr>
                                <td>Выписано ноотропов</td>
                                <td>{{ $statistics[11] }}</td>
                            </tr>
                            <tr>
                                <td>Выписано холинолитиков</td>
                                <td>{{ $statistics[5] }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <p id="strict_message" class="pt-2 borderpb-2 mb-4 text-danger border-bottom border-danger fs-6">
                        <i class="bi bi-info-circle"></i> Обновление данных в таблице происходит через несколько секунд. Перезагрузите страницу для получения обновлений.
                    </p>
                    <h3>Выписанные рецепты</h3>
                    <table class="styled-table">
                        <thead>
                            <tr>
                                <th>Пациент</th>
                                <th>Лекарство</th>
                                <th>Дата выписки</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($prescriptions as $prescription)
                                <tr>
                                    <td>{{ $prescription->patient_name }}</td>
                                    <td>{{ $prescription->russian_generic_name }}</td>
                                    <td>{{ $prescription->issued_at }}</td>
                                    <td>
                                        <button class="btn" type="button">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-print" type="button" data-id="{{ $prescription->id }}">
                                            <i class="bi bi-printer"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $prescriptions->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('#patient-select').selectize({
                create: false,
                valueField: 'id',
                labelField: 'name',
                searchField: 'name',
                placeholder: 'Начните вводить имя пациента',
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.btn-print').forEach(button => {
                button.addEventListener('click', function () {
                    const prescriptionId = this.getAttribute('data-id');

                    fetch(`/prescriptions/print/${prescriptionId}`, {
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                        .then(response => response.text())
                        .then(html => {
                            const iframe = document.createElement('iframe');
                            iframe.style.display = 'none';
                            document.body.appendChild(iframe);

                            const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                            iframeDoc.open();
                            iframeDoc.write(html);
                            iframeDoc.close();

                            iframe.onload = function () {
                                iframe.contentWindow.print();
                                document.body.removeChild(iframe);
                            };
                        })
                        .catch(error => {
                            console.error('Ошибка при печати рецепта:', error);
                            alert('Не удалось распечатать рецепт. Попробуйте снова.');
                        });
                });
            });
        });
    </script>
@endsection
