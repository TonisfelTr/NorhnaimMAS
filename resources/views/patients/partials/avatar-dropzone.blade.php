{{-- Place inside the EXISTING avatar upload <form>. Do not create a second form. --}}
<div class="nh-avatar-upload" data-avatar-upload data-max-bytes="524288">
    <div class="nh-avatar-upload__preview">
        <img class="nh-avatar-upload__image {{ empty($avatarUrl) ? 'd-none' : '' }}"
             data-avatar-preview-image
             src="{{ $avatarUrl ?: '' }}"
             alt="Предпросмотр фотографии профиля">
        <span class="nh-avatar-upload__initials {{ !empty($avatarUrl) ? 'd-none' : '' }}"
              data-avatar-preview-initials>{{ $avatarInitials ?? '' }}</span>
        <span class="nh-avatar-upload__preview-check" aria-hidden="true"><i class="bi bi-check-lg"></i></span>
    </div>

    <label class="nh-avatar-upload__zone" data-avatar-dropzone>
        <input class="nh-avatar-upload__input"
               type="file"
               name="{{ $avatarFieldName ?? 'avatar' }}"
               accept="image/jpeg,image/png,image/webp"
               data-avatar-file
               aria-describedby="avatarUploadHelp avatarUploadStatus">
        <span class="nh-avatar-upload__zone-icon" aria-hidden="true"><i class="bi bi-cloud-arrow-up"></i></span>
        <span class="nh-avatar-upload__zone-title">Перетащите фотографию сюда</span>
        <span class="nh-avatar-upload__zone-subtitle">или <span class="nh-avatar-upload__zone-link">выберите файл</span></span>
        <span class="nh-avatar-upload__formats" id="avatarUploadHelp">JPG, PNG, WEBP · до 512 КБ</span>
    </label>

    <div class="nh-avatar-upload__status" id="avatarUploadStatus" data-avatar-status aria-live="polite">Файл не выбран</div>
    <button class="nh-avatar-upload__submit" data-avatar-submit type="submit" disabled>
        <i class="bi bi-check2-circle" aria-hidden="true"></i>
        Сохранить фотографию
    </button>
</div>
