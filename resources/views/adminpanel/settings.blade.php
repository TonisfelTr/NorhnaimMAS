@extends('layouts.admin')
@section('title', 'Настройки')
@section('assets')
@endsection

@section('main')
    <div class="container-fluid">
        <h1>Настройки</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                {{ Breadcrumbs::render('admin.settings') }}
            </ol>
        </nav>
        @if($errors->isNotEmpty())
            <div class="alert alert-danger">
                При сохранении конфигурации, возникли следующие ошибки:
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @elseif(session()->has('status') && session()->get('status') == 'settings.success')
            <div class="alert alert-success">
                Изменения настроек были сохранены!
            </div>
        @endif
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="mail-tab" data-bs-toggle="tab" data-bs-target="#mail-tab-pane" type="button" role="tab" aria-controls="mail-tab-pane" aria-selected="true">
                    <i class="bi bi-inboxes-fill"></i> Почта
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="blog-tab" data-bs-toggle="tab" data-bs-target="#blog-tab-pane" type="button" role="tab" aria-controls="blog-tab-pane" aria-selected="false">
                    <i class="bi bi-chat-left-text"></i> Блог
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo-tab-pane" type="button" role="tab" aria-controls="seo-tab-pane" aria-selected="false">
                    <i class="bi bi-badge-ad"></i> SEO
                </button>
            </li>
        </ul>
        <form method="post" action="{{ route('admin.settings.save') }}" autocomplete="off" class="p-4 bg-white">
            @csrf
            @recaptcha
            <div class="tab-content bg-white" id="settingsTabContent">
                <div class="tab-pane fade show active" id="mail-tab-pane" role="tabpanel" aria-labelledby="mail-tab" tabindex="0">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mail-host" class="label-control mb-2">Хост</label>
                                    <input class="form-control" type="text" name="mail-host" id="mail-host" autocomplete="nope" value="{{ setting('mail-host') }}">
                                </div>
                                <div class="mb-3">
                                    <label for="mail-port" class="label-control mb-2">Порт</label>
                                    <input class="form-control" type="number" name="mail-port" id="mail-port" autocomplete="nope" value="{{ setting('mail-port') }}">
                                </div>
                                <div class="mb-3">
                                    <label for="mail-username" class="label-control mb-2">Пользователь</label>
                                    <input class="form-control" type="email" name="mail-username" id="mail-username" autocomplete="nope" value="{{ setting('mail-username') }}">
                                </div>
                                <div class="mb-3">
                                    <label for="mail-password" class="label-control mb-2">Пароль</label>
                                    <input class="form-control" type="password" name="mail-password" id="mail-password" autocomplete="nope">
                                </div>
                                <div class="mb-3">
                                    <label for="mail-encryption" class="label-control mb-2">Шифрование</label>
                                    <select class="form-control" name="mail-encryption" id="mail-encryption">
                                        <option value="ssl" @if(setting('mail-encryption') == 'ssl') selected @endif>SSL</option>
                                        <option value="tls" @if(setting('mail-encryption') == 'tls') selected @endif>TLS</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="mail-from-address" class="label-control mb-2">От кого (адрес электронной почты)</label>
                                    <input class="form-control" type="email" name="mail-from-address" id="mail-from-address" autocomplete="nope" value="{{ setting('mail-from-address') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="blog-tab-pane" role="tabpanel" aria-labelledby="blog-tab" tabindex="0">...</div>
                <div class="tab-pane fade" id="seo-tab-pane" role="tabpanel" aria-labelledby="seo-tab" tabindex="0">
                    <div class="container_fluid">
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Заголовок/Title</label>
                                    <input class="form-control" id="title" type="text" name="title">
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Описание/Description</label>
                                    <input class="form-control" id="description" type="text" name="description">
                                </div>
                                <div class="mb-3">
                                    <label for="og_title" class="form-label">Open Graph Заголовок/Title</label>
                                    <input class="form-control" id="og_title" type="text" name="og_title">
                                </div>
                                <div class="mb-3">
                                    <label for="og_description" class="form-label">Open Graph Описание/Description</label>
                                    <input class="form-control" id="og_description" type="text" name="og_description">
                                </div>
                                <div class="mb-3">
                                    <label for="og_type" class="form-label">Open Graph тип объекта/Type</label>
                                    <input class="form-control" id="og_type" type="text" name="og_type">
                                </div>
                                <div class="mb-3">
                                    <label for="og_image" class="form-label">Open Graph Картинка/Image</label>
                                    <input class="form-control" id="og_image" type="text" name="og_image">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="twitter_title" class="form-label">Twitter Заголовок/Title</label>
                                    <input class="form-control" id="twitter_title" type="text" name="twitter_title">
                                </div>
                                <div class="mb-3">
                                    <label for="twitter_card" class="form-label">Twitter Тип карточки/Card</label>
                                    <input class="form-control" id="twitter_card" type="text" name="twitter_card">
                                </div>
                                <div class="mb-3">
                                    <label for="twitter_description" class="form-label">Twitter Описание/Description</label>
                                    <input class="form-control" id="twitter_description" type="text" name="twitter_description">
                                </div>
                                <div class="mb-3">
                                    <label for="twitter_image" class="form-label">Twitter Картинка/Image</label>
                                    <input class="form-control" id="twitter_image" type="text" name="twitter_image">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="btn-group mt-5">
                <button class="btn btn-success" type="submit"><i class="bi bi-save"></i> Сохранить</button>
            </div>
        </form>
    </div>
@endsection
