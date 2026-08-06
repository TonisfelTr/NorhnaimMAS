<?php

declare(strict_types=1);

namespace App\Services\Prescriptions;

use App\Models\Prescription;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class PrescriptionRepeatCreator
{
    /**
     * Создаёт новый рецепт на основании предыдущего.
     *
     * Передавайте сюда только уже проверенные данные формы.
     */
    public function createFrom(
        Prescription $source,
        int $doctorId,
        array $validatedData
    ): Prescription {
        if ($doctorId <= 0) {
            throw new InvalidArgumentException('Не указан врач нового рецепта.');
        }

        unset(
            $validatedData['id'],
            $validatedData['doctor_id'],
            $validatedData['patient_id'],
            $validatedData['repeated_from_id'],
            $validatedData['created_at'],
            $validatedData['updated_at']
        );

        return DB::transaction(function () use (
            $source,
            $doctorId,
            $validatedData
        ): Prescription {
            $newPrescription = new Prescription();

            $newPrescription->forceFill(array_merge($validatedData, [
                'doctor_id' => $doctorId,
                'patient_id' => $source->patient_id,
                'repeated_from_id' => $source->getKey(),
            ]));

            $newPrescription->save();

            return $newPrescription->refresh();
        });
    }

    /**
     * Помечает уже созданный существующей логикой рецепт как повторный.
     *
     * Используйте этот метод, если ваш текущий action сначала создаёт копию,
     * а затем перенаправляет пользователя на её редактирование.
     */
    public function markExisting(
        Prescription $newPrescription,
        Prescription $source,
        int $doctorId
    ): Prescription {
        if ($newPrescription->exists === false || $source->exists === false) {
            throw new InvalidArgumentException('Оба рецепта должны быть сохранены.');
        }

        if ((int)$newPrescription->getKey() === (int)$source->getKey()) {
            throw new InvalidArgumentException(
                'Рецепт не может быть повторно оформлен на основании самого себя.'
            );
        }

        if ((int)$newPrescription->patient_id !== (int)$source->patient_id) {
            throw new InvalidArgumentException(
                'Исходный и новый рецепт должны принадлежать одному пациенту.'
            );
        }

        $newPrescription->forceFill([
            'doctor_id' => $doctorId,
            'repeated_from_id' => $source->getKey(),
        ])->save();

        return $newPrescription->refresh();
    }
}
