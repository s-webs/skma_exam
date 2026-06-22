<x-mail::message>
# Запись на экзамен одобрена

**Экзамен:** {{ $examName }}

**Время на прохождение:** {{ $durationMinutes }} мин.

<x-mail::button :url="$examUrl">
Перейти к экзамену
</x-mail::button>

Ссылка действительна до начала экзамена. Не передавайте её третьим лицам.

С уважением,<br>
{{ config('app.name') }}
</x-mail::message>
