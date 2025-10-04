@if(!is_authed())
    <div class="modal fade" id="authorization-block" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="authorization-block__label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="authorization-block__label">Авторизация</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form class="modal-body row" enctype="multipart/form-data" action="{{ route('actions.login') }}" method="POST">
                    @if(session('open_modal') == 'authorization-block')
                        <div class="alert alert-danger">
                            <p>При авторизации произошли следующие ошибки:</p>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @csrf
                    @recaptcha
                    <div class="col-md-5">
                        <div class="mb-3">
                            <label for="authorization-block__email" class="form-label">Электронная почта</label>
                            <input class="form-control" id="authorization-block__email" type="email" name="email">
                        </div>
                        <div class="mb-2">
                            <label for="authorization-block__password" class="form-label">Пароль</label>
                            <input class="form-control" id="authorization-block__password" type="password" name="password">
                        </div>
                        <x-line-check-box label="Запомнить меня" name="remember_me" />
                    </div>
                    <div class="col-md-6 ml-3">
                        Some info
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-dark" type="submit">Авторизация</button>
                        <button class="btn btn-light" type="button" data-bs-dismiss="modal">Закрыть</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @if(session('open_modal') == 'authorization-block')
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                var myModal = new bootstrap.Modal(document.getElementById('authorization-block'), {
                    keyboard: false
                });
                myModal.show();
            });
        </script>
    @endif
@endif
