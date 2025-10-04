<div class="{{ $wrapperClass }} d-inline-flex doctor-checkbox__wrapper">
    <input class="d-none" type="checkbox" name="{{ $name }}" id="{{ $id }}" {{ $checked ? 'checked' : '' }}>
    <div class="doctor-checkbox__body {{ $checked ? 'checked' : '' }}">
        <span>&#10003;</span>
    </div>
    <label for="{{ $id }}">{{ $label }}</label>
</div>
@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.doctor-checkbox__body').forEach(el => {
                    const input = el.previousElementSibling;
                    if (input && input.type === 'checkbox') {
                        const updateClass = () => {
                            el.classList.toggle('checked', input.checked);
                        }

                        input.addEventListener('change', updateClass);
                        el.addEventListener('click', () => {
                            input.checked = !input.checked;
                            updateClass();
                        });

                        updateClass(); // начальное состояние
                    }
                });
            });
        </script>
    @endpush
@endonce
