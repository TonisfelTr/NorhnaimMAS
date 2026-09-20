<?php

namespace App\Support\CardDocuments;

final class DocumentCatalog
{
    public const TYPES = [
        'psychiatrist' => [
            'title' => 'Заключение психиатра',
            'hint' => 'Универсальное медицинское заключение',
            'icon' => 'bi-file-earmark-medical',
            'fields' => [
                'complaints' => 'Жалобы',
                'anamnesis' => 'Анамнестические сведения',
                'mental_status' => 'Психический статус',
                'conclusion' => 'Заключение',
                'recommendations' => 'Рекомендации',
            ],
            'required' => ['mental_status', 'conclusion'],
        ],
        'extract' => [
            'title' => 'Выписка из медкарты',
            'hint' => 'За период или всё время наблюдения',
            'icon' => 'bi-file-text',
            'fields' => [
                'summary' => 'Краткие сведения о наблюдении и лечении',
                'course' => 'Течение заболевания и динамика состояния',
                'treatment' => 'Проведённое лечение',
                'outcome' => 'Состояние на дату составления выписки',
                'recommendations' => 'Рекомендации',
            ],
            'required' => ['summary'],
        ],
        'certificate' => [
            'title' => 'Справка',
            'hint' => 'Медицинские справки по месту требования',
            'icon' => 'bi-patch-check',
            'fields' => [
                'body' => 'Текст справки',
                'notes' => 'Дополнительные сведения',
            ],
            'required' => ['body'],
        ],
        'continuation' => [
            'title' => 'Для продолжения лечения',
            'hint' => 'Передача сведений другому врачу',
            'icon' => 'bi-arrow-left-right',
            'fields' => [
                'reason' => 'Причина передачи и цель дальнейшего лечения',
                'history' => 'Краткий анамнез и течение заболевания',
                'current_state' => 'Состояние пациента на момент передачи',
                'current_therapy' => 'Актуальная терапия — проверяется врачом',
                'intolerance' => 'Известная непереносимость и ограничения',
                'follow_up' => 'План дальнейшего наблюдения и лечения',
            ],
            'required' => ['reason', 'current_state', 'follow_up'],
        ],
    ];

    public const SOURCES = [
        'anamneses' => 'Записи анамнеза',
        'epicrises' => 'Сохранённые эпикризы',
        'prescriptions' => 'История выписанных рецептов',
        'labs' => 'Готовые лабораторные результаты',
        'instrumentals' => 'Готовые инструментальные исследования',
    ];

    public static function menu(): array
    {
        $items = self::TYPES;
        $epicrisis = [
            'epicrisis' => [
                'title' => 'Эпикриз',
                'hint' => 'Этапный, переводной или выписной',
                'icon' => 'bi-journal-medical',
            ],
        ];

        return array_slice($items, 0, 2, true)
            + $epicrisis
            + array_slice($items, 2, null, true);
    }

    public static function get(string $type): array
    {
        if (!isset(self::TYPES[$type])) {
            throw new \InvalidArgumentException('Неизвестный тип медицинского документа.');
        }

        return self::TYPES[$type];
    }
}
