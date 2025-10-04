<?php

namespace App\Traits;

/**
 * Трейт для работы с ФИО полями модели.
 */
trait FullnameConverter {
    /**
     * Получить полное имя.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->surname}, {$this->name} {$this->patronym}";
    }

    /**
     * Получить сокращённое полное имя (без отчества).
     *
     * @return string
     */
    public function trimmedName(): string
    {
        return "{$this->surname}, {$this->name}";
    }

    /**
     * Получить полное имя с инициалами.
     *
     * @param bool $surnameFirst Вставить фамилию в начале строки. Если false, фамилия будет после инициалов.
     * @return string
     */
    public function getFullNameWithInitials(bool $surnameFirst = true): string
    {
        $result = !$surnameFirst ?: $this->surname . ' ';
        $result .= mb_substr($this->name, 0, 1) . '. ' . mb_substr($this->patronym, 0, 1);
        $result .= !$surnameFirst ? $this->surname : '';

        return $result;
    }
}
