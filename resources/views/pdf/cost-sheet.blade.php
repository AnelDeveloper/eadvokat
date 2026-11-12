@php
    use App\Enums\ItemStatus;
    use App\Support\Format;
@endphp
<!DOCTYPE html>
<html lang="bs">
<head>
    <meta charset="utf-8">
    <title>{{ __('costs.pdf.title') }} · {{ $matter->case_number }}</title>
    <style>
        @page { margin: 28mm 18mm 22mm; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10pt; color: #173247; }
        h1 { font-size: 15pt; margin: 0 0 4pt; letter-spacing: .04em; }
        .notice { border: 1px dashed #B27B34; background: #F7EEDF; color: #7A4F17; padding: 6pt 8pt; margin: 8pt 0 12pt; }
        .meta td { padding: 2pt 10pt 2pt 0; vertical-align: top; }
        .meta td:first-child { color: #4F6676; white-space: nowrap; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 12pt; border-top: 2px solid #173247; }
        table.items th { text-align: left; font-size: 8.5pt; color: #4F6676; padding: 5pt 4pt; border-bottom: 1px solid #D5DDE2; }
        table.items td { padding: 6pt 4pt; border-bottom: 1px solid #D5DDE2; vertical-align: top; }
        .num { text-align: right; white-space: nowrap; font-family: 'DejaVu Sans Mono', monospace; }
        .proposed { color: #7A4F17; }
        .total td { font-weight: bold; border-bottom: 0; }
        .footer { margin-top: 16pt; color: #4F6676; font-size: 8.5pt; }
    </style>
</head>
<body>
    <h1>{{ __('costs.pdf.title') }}</h1>
    <div class="notice">
        {{ __('costs.pdf.notice') }}
        @if ($isDemo)<br>{{ __('costs.pdf.demo') }}@endif
    </div>

    <table class="meta">
        <tr><td>{{ __('costs.pdf.matter') }}</td><td>{{ $matter->case_number }} · {{ $matter->title }}</td></tr>
        <tr><td>{{ __('costs.pdf.court') }}</td><td>{{ $matter->court }}</td></tr>
        <tr><td>{{ __('costs.pdf.jurisdiction') }}</td><td>{{ $sheet->jurisdiction->label() }}</td></tr>
        <tr><td>{{ __('costs.pdf.tariff') }}</td><td>{{ $sheet->tariff_source ?: '—' }}</td></tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>{{ __('costs.pdf.date') }}</th>
                <th>{{ __('costs.pdf.item') }}</th>
                <th>{{ __('costs.pdf.source') }}</th>
                <th>{{ __('costs.pdf.status') }}</th>
                <th class="num">{{ __('costs.pdf.amount') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sheet->items as $item)
                <tr>
                    <td class="num">{{ Format::date($item->action_date) }}</td>
                    <td>{{ $item->title }}</td>
                    <td>{{ $item->source_label }}</td>
                    <td class="{{ $item->status === ItemStatus::Confirmed ? '' : 'proposed' }}">
                        {{ $item->status === ItemStatus::Confirmed ? __('costs.pdf.status_confirmed') : __('costs.pdf.status_proposed') }}
                    </td>
                    <td class="num">{{ $item->amount === null ? '—' : Format::money($item->amount) }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td colspan="4">{{ __('costs.pdf.total') }}</td>
                <td class="num">{{ $total }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        {{ $approvedLine }}<br>
        {{ $generated }}
    </div>
</body>
</html>
