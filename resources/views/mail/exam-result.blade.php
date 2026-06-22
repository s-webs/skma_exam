<x-mail::message>
# {{ __('exam_report.exam_result') }}

**{{ __('exam_report.exam') }}:** {{ $examName }}

**{{ __('exam_report.result') }}:** {{ $score }}%

**{{ __('exam_report.status') }}:** {{ $passed ? __('exam_report.passed') : __('exam_report.failed') }}

<x-mail::button :url="$reportUrl">
{{ __('exam_report.pdf_link') }}
</x-mail::button>

{{ config('app.name') }}
</x-mail::message>
