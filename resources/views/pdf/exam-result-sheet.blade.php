<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>{{ __('exam_report.title', [], 'ru') }}</title>
    <style>
        @page { margin: 24px 28px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        .locale-block { margin-bottom: 22px; page-break-inside: avoid; }
        .locale-block + .locale-block { border-top: 1px solid #ccc; padding-top: 18px; }
        .header-row { width: 100%; margin-bottom: 12px; }
        .header-row td { vertical-align: top; }
        .title { font-size: 15px; font-weight: bold; text-transform: uppercase; margin: 0 0 8px 0; }
        .meta { margin: 4px 0; }
        .meta strong { font-weight: bold; }
        .qr { text-align: right; }
        .qr img { width: 88px; height: 88px; }
        .qr-hint { font-size: 8px; color: #555; margin-top: 4px; max-width: 100px; margin-left: auto; }
        .section-title { font-weight: bold; margin: 14px 0 8px 0; font-size: 12px; }
        table.results { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.results th, table.results td { border: 1px solid #333; padding: 6px 8px; text-align: left; }
        table.results th { background: #f0f0f0; font-weight: bold; }
        .result-row td { font-weight: bold; }
        .status { margin-top: 8px; }
    </style>
</head>
<body>
@foreach ($locales as $locale)
    @php app()->setLocale($locale); @endphp
    <div class="locale-block">
        <table class="header-row" width="100%">
            <tr>
                <td width="75%">
                    <p class="title">{{ __('exam_report.title') }}</p>
                    <p class="meta">{{ __('exam_report.based_on', ['id' => $attempt->id, 'date' => $completedDate]) }}</p>
                    <p class="meta"><strong>{{ __('exam_report.full_name') }}:</strong> {{ $applicant->name }}</p>
                </td>
                <td width="25%" class="qr">
                    <img src="{{ $qrDataUri }}" alt="QR">
                    <p class="qr-hint">{{ __('exam_report.scan_hint') }}</p>
                </td>
            </tr>
        </table>

        <p class="section-title">{{ __('exam_report.exam_result') }}</p>

        <table class="results">
            <thead>
                <tr>
                    <th>{{ __('exam_report.subject') }}</th>
                    <th>{{ __('exam_report.points') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $exam->name }}</td>
                    <td>{{ $result->correct_answers }}</td>
                </tr>
                <tr class="result-row">
                    <td>{{ __('exam_report.result') }}</td>
                    <td>{{ $result->total_score }}</td>
                </tr>
            </tbody>
        </table>

        <p class="status">
            <strong>{{ __('exam_report.status') }}:</strong>
            {{ $result->passed ? __('exam_report.passed') : __('exam_report.failed') }}
        </p>
    </div>
@endforeach
</body>
</html>
