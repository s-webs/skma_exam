<x-mail::message>
# {{ __('mail.exam_invite.title') }}

**{{ __('mail.exam_invite.exam') }}:** {{ $examName }}

**{{ __('mail.exam_invite.duration', ['minutes' => $durationMinutes]) }}**

<x-mail::button :url="$examUrl">
{{ __('mail.exam_invite.button') }}
</x-mail::button>

{{ __('mail.exam_invite.link_hint') }}

{{ config('app.name') }}
</x-mail::message>
