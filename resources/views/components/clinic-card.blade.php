<div class="card mb-3" style="max-width: 100%">
    <div class="row g-0">
        <div class="col-md-4">
            <picture>
                <source srcset="{{ asset('assets/images/backgrounds/feedback_placeholder.webp') }}" type="image/webp">
                <img src="{{ asset('assets/images/backgrounds/feedback_placeholder.png') }}" class="img-fluid rounded-start" alt="Нет фотографии">
            </picture>
        </div>
        <div class="col-md-8">
            <div class="card-body">
                <h5 class="card-title">{{ $clinic_name }}</h5>
                <p class="card-text">{{ $clinic_description }}</p>
            </div>
        </div>
    </div>
</div>
