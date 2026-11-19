{{-- Этот partial располагается внутри существующей формы загрузки аватара. --}}
<div class="nh-avatar-v2" data-avatar-uploader data-max-size="5242880">
    <label class="nh-avatar-v2__zone" for="patientProfileAvatar" data-avatar-dropzone>
        <input
            id="patientProfileAvatar"
            class="nh-avatar-v2__input"
            type="file"
            name="avatar"
            accept="image/jpeg,image/png,image/webp"
            aria-describedby="patientAvatarV2Hint patientAvatarV2Status"
            data-avatar-input
            required
        >

        <span class="nh-avatar-v2__preview" aria-hidden="true">
            <img
                class="nh-avatar-v2__photo {{ $avatarUrl ? '' : 'd-none' }}"
                src="{{ $avatarUrl ?: '' }}"
                alt=""
                data-avatar-photo
            >
            <span class="nh-avatar-v2__initials {{ $avatarUrl ? 'd-none' : '' }}" data-avatar-initials>
                {{ $initials }}
            </span>
            <span class="nh-avatar-v2__camera"><i class="bi bi-camera-fill"></i></span>
        </span>

        <span class="nh-avatar-v2__title">Перетащите фотографию сюда</span>
        <span class="nh-avatar-v2__subtitle">
            или <span class="nh-avatar-v2__link">нажмите для выбора</span>
        </span>
        <span class="nh-avatar-v2__hint" id="patientAvatarV2Hint">JPG, PNG или WEBP · до 5 МБ</span>
    </label>

    <p class="nh-avatar-v2__status" id="patientAvatarV2Status" data-avatar-status role="status" aria-live="polite">
        Фото ещё не выбрано
    </p>

    <button class="nh-avatar-v2__save" data-avatar-save type="submit" disabled>
        <i class="bi bi-check2-circle" aria-hidden="true"></i>
        <span data-avatar-save-text>Сохранить фотографию</span>
    </button>
</div>
