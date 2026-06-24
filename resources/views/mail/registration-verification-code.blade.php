<x-mail::message>
# {{ __('mail.verification.title') }}

{{ __('mail.verification.code_intro') }}

<x-mail::panel>
**{{ $code }}**
</x-mail::panel>

{{ __('mail.verification.expiry') }}

{{ config('app.name') }}
</x-mail::message>
