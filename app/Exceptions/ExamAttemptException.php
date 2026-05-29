<?php

namespace App\Exceptions;

use Exception;

class ExamAttemptException extends Exception
{
    public static function noTelegram(): self
    {
        return new self('У абитуриента не привязан Telegram. Одобрение невозможно.');
    }

    public static function examInactive(): self
    {
        return new self('Экзамен неактивен.');
    }

    public static function notEnoughQuestions(int $required, int $available): self
    {
        return new self("Недостаточно активных вопросов в банке экзамена (нужно {$required}, доступно {$available}).");
    }

    public static function maxAttemptsReached(): self
    {
        return new self('Достигнут лимит попыток прохождения экзамена.');
    }

    public static function activeAttemptExists(): self
    {
        return new self('У абитуриента уже есть активная попытка или отправленная ссылка на этот экзамен.');
    }

    public static function registrationNotApproved(): self
    {
        return new self('Запись на экзамен не одобрена.');
    }

    public static function invalidStatus(string $status): self
    {
        return new self("Недопустимый статус попытки: {$status}.");
    }

    public static function expired(): self
    {
        return new self('Время экзамена истекло.');
    }

    public static function alreadyCompleted(): self
    {
        return new self('Экзамен уже завершён.');
    }
}
