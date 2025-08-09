<?php

namespace App\Enums;

enum StatusEnum: string
{
   case ALLOWED = 'Allowed';
   case PROHIBITED = 'Prohibited';

    /**
     * Возвращает читаемое название статуса (локализация)
     */
    public function toName(): string
    {
        return match($this) {
            self::ALLOWED => 'Allowed',
            self::PROHIBITED => 'Prohibited',
        };
    }
}
