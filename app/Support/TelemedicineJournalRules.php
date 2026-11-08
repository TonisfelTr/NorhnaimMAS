<?php

namespace App\Support;

/** Pure rules: the UI and the write handler use exactly the same decision. */
final class TelemedicineJournalRules
{
    /** @return array{can_delete:bool,state:string,label:string,reason:string} */
    public static function deletion(
        bool $canManage,
        bool $deleted,
        ?\DateTimeInterface $startsAt,
        ?string $confirmation,
        ?string $consultation,
        bool $hasClinicalDocument,
        bool $openConnection,
        \DateTimeInterface $now
    ): array {
        if ($deleted) {
            return self::result(false, 'deleted', 'Удалён', 'Сеанс уже удалён из действующего расписания.');
        }
        if ($confirmation === 'held' || $consultation === 'completed') {
            return self::result(false, 'held', 'Состоялся',
                'Удаление запрещено: приём подтверждён или консультация завершена.');
        }
        if ($hasClinicalDocument) {
            return self::result(false, 'protected', 'Есть протокол',
                'Удаление запрещено: запись содержит медицинский протокол.');
        }
        if ($consultation === 'in_progress' || $openConnection) {
            return self::result(false, 'in_progress', 'Проводится',
                'Сначала завершите консультацию и подключения участников.');
        }
        if (!in_array($confirmation, [null, '', 'not_held'], true)
            || !in_array($consultation, [null, '', 'not_started', 'not_held'], true)) {
            return self::result(false, 'unknown', 'Проверьте статус',
                'Неизвестный статус приёма: удаление заблокировано.');
        }
        if ($startsAt === null) {
            return self::result(false, 'unknown', 'Дата не указана',
                'У записи не указана дата начала.');
        }
        $future = $startsAt->getTimestamp() > $now->getTimestamp();
        $notHeld = $confirmation === 'not_held' || $consultation === 'not_held';
        if (!$future && !$notHeld) {
            return self::result(false, 'pending', 'Результат не отмечен',
                'Время приёма уже наступило. Сначала отметьте «Не состоялся», если консультации не было.');
        }
        $state = $notHeld ? 'not_held' : 'scheduled';
        $label = $notHeld ? 'Не состоялся' : 'Запланирован';
        return self::result($canManage, $state, $label,
            $canManage ? '' : 'Нет права изменять эту медицинскую карту.');
    }

    private static function result(bool $allowed, string $state, string $label, string $reason): array
    {
        return ['can_delete' => $allowed, 'state' => $state, 'label' => $label, 'reason' => $reason];
    }

    /** Unclosed technical events are not proof of a completed consultation. */
    public static function hasOpenConnection(iterable $events): bool
    {
        $rooms = [];
        foreach ($events as $event) {
            $event = (array) $event;
            $room = (string) ($event['room_id'] ?? 'unknown');
            $role = (string) ($event['participant_role'] ?? '');
            $type = (string) ($event['event_type'] ?? '');
            if ($type === 'call_ended') {
                $rooms[$room] = [];
            } elseif (in_array($role, ['doctor', 'patient'], true)) {
                if (in_array($type, ['participant_joined', 'media_connected'], true)) {
                    $rooms[$room][$role] = true;
                } elseif (in_array($type, ['participant_left', 'connection_failed'], true)) {
                    unset($rooms[$room][$role]);
                }
                // media_disconnected alone does not mean that the user left.
            }
        }
        foreach ($rooms as $roles) {
            if ($roles !== []) {
                return true;
            }
        }
        return false;
    }
}
