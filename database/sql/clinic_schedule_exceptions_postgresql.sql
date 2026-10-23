CREATE TABLE IF NOT EXISTS clinic_schedule_exceptions (
    id BIGSERIAL PRIMARY KEY,
    clinic_id BIGINT NOT NULL REFERENCES clinics(id) ON DELETE CASCADE,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    date_from DATE NOT NULL,
    date_to DATE NOT NULL,
    all_day BOOLEAN NOT NULL DEFAULT FALSE,
    time_from TIME NULL,
    time_to TIME NULL,
    mode VARCHAR(30) NOT NULL,
    scope VARCHAR(30) NOT NULL,
    target_ids JSON NULL,
    comment TEXT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE NULL
);

CREATE INDEX IF NOT EXISTS clinic_schedule_exceptions_clinic_dates_idx
    ON clinic_schedule_exceptions (clinic_id, date_from, date_to);
CREATE INDEX IF NOT EXISTS clinic_schedule_exceptions_clinic_scope_idx
    ON clinic_schedule_exceptions (clinic_id, scope);
