<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>{{ __('exam_report.title', [], 'ru') }}</title>
    <style>
        @page { margin: 88px 28px 72px 28px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; margin: 0; }
        .pdf-header {
            position: fixed;
            top: -72px;
            left: 0;
            right: 0;
            height: 56px;
            text-align: center;
        }
        .pdf-header img { height: 48px; width: auto; max-width: 100%; }
        .pdf-footer {
            position: fixed;
            bottom: -60px;
            left: 0;
            right: 0;
            height: 48px;
            text-align: center;
            border-top: 1px solid #ccc;
            padding-top: 8px;
        }
        .pdf-footer img { height: 32px; width: auto; opacity: 0.85; }
        .doc-header { width: 100%; margin-bottom: 16px; }
        .doc-header td { vertical-align: top; }
        .header-locale + .header-locale { margin-top: 8px; padding-top: 8px; border-top: 1px solid #e5e5e5; }
        .title { font-size: 14px; font-weight: bold; text-transform: uppercase; margin: 0 0 6px 0; }
        .meta { margin: 3px 0; }
        .meta strong { font-weight: bold; }
        .qr { text-align: right; vertical-align: top; }
        .qr img { width: 88px; height: 88px; }
        .locale-block { margin-bottom: 20px; page-break-inside: avoid; }
        .locale-block + .locale-block { border-top: 1px solid #ccc; padding-top: 16px; }
        .section-title { font-weight: bold; margin: 0 0 8px 0; font-size: 12px; }
        table.summary { width: 100%; border-collapse: collapse; margin-top: 4px; }
        table.summary th, table.summary td { border: 1px solid #333; padding: 6px 8px; text-align: left; }
        table.summary th { background: #f0f0f0; font-weight: bold; width: 55%; }
    </style>
</head>
<body>
@if ($logoDataUri)
    <div class="pdf-header">
        <img src="{{ $logoDataUri }}" alt="SKMA">
    </div>
    <div class="pdf-footer">
        <img src="{{ $logoDataUri }}" alt="SKMA">
    </div>
@endif

<table class="doc-header" width="100%">
    <tr>
        <td width="72%">
            @foreach ($locales as $locale)
                @php app()->setLocale($locale); @endphp
                <div class="header-locale">
                    <p class="title">{{ __('exam_report.title') }}</p>
                    <p class="meta">{{ __('exam_report.based_on', ['id' => $attempt->id, 'date' => $completedDate]) }}</p>
                    <p class="meta"><strong>{{ __('exam_report.full_name') }}:</strong> {{ $applicant->name }}</p>
                </div>
            @endforeach
        </td>
        <td width="28%" class="qr">
            <img src="{{ $qrDataUri }}" alt="QR">
        </td>
    </tr>
</table>

@foreach ($locales as $locale)
    @php app()->setLocale($locale); @endphp
    <div class="locale-block">
        <p class="section-title">{{ __('exam_report.exam_result') }}</p>

        <table class="summary">
            <tbody>
                <tr>
                    <th>{{ __('exam_report.exam_type_and_name') }}</th>
                    <td>{{ $exam->examType?->localizedName($exam->language) ?? '—' }} / {{ $exam->localizedName($exam->language) }}</td>
                </tr>
                <tr>
                    <th>{{ __('exam_report.correct_answers') }}</th>
                    <td>{{ $result->correct_answers }} / {{ $result->total_questions }}</td>
                </tr>
                <tr>
                    <th>{{ __('exam_report.total_score') }}</th>
                    <td>{{ $result->total_score }}%</td>
                </tr>
                <tr>
                    <th>{{ __('exam_report.passing_threshold') }}</th>
                    <td>{{ $result->passing_score }}</td>
                </tr>
                <tr>
                    <th>{{ __('exam_report.status') }}</th>
                    <td>{{ $result->passed ? __('exam_report.passed') : __('exam_report.failed') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
@endforeach
</body>
</html>
