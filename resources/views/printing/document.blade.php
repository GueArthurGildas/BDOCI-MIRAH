<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $document->basename }}</title>
    <style>
        @page { margin: 2.5cm 2.5cm 2.2cm 2.5cm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #000; }
        .first-header { text-align: center; margin-bottom: 14px; }
        .first-header div { margin: 0; }
        .first-header .strong { font-weight: bold; font-size: 11px; }
        .first-header .file { font-style: italic; font-size: 8px; }
        footer { position: fixed; bottom: -1.4cm; left: 0; right: 0; text-align: center; font-size: 7px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 10px; page-break-inside: auto; }
        tr { page-break-inside: avoid; }
        td { border: 0.6px solid #000; padding: 2px 4px; vertical-align: top; word-wrap: break-word; }
        td div { margin: 0; }
        p { margin: 0 0 4px; }
        ul { margin: 0 0 8px 16px; padding: 0; }
    </style>
</head>
<body>
    <footer>{{ $document->filename('pdf') }}</footer>

    <div class="first-header">
        @foreach ($document->firstPageHeader as $line)
            <div @class(['strong' => $loop->index < 2])>{{ $line }}</div>
        @endforeach
        <div class="file">{{ $document->filename('pdf') }}</div>
    </div>

    @foreach ($document->blocks() as $block)
        @if ($block['type'] === 'table')
            <table>
                <colgroup>
                    @foreach ($block['widths'] as $width)
                        <col style="width: {{ $width }}%">
                    @endforeach
                </colgroup>
                @foreach ($block['rows'] as $row)
                    @php($column = 0)
                    <tr>
                        @foreach ($row as $cell)
                            @php($width = array_sum(array_slice($block['widths'], $column, $cell['span'])))
                            @php($column += $cell['span'])
                            <td colspan="{{ $cell['span'] }}" style="width: {{ $width }}%;{{ $cell['fill'] ? ' background: #'.$cell['fill'].';' : '' }}">
                                @foreach ($cell['lines'] as $line)
                                    @foreach (explode("\n", $line['text']) as $text)
                                        <div style="{{ $line['color'] ? 'color: #'.$line['color'].';' : '' }}{{ $line['bold'] ? 'font-weight: bold;' : '' }}{{ $line['italic'] ? 'font-style: italic;' : '' }}{{ $line['strike'] ? 'text-decoration: line-through;' : '' }}">{{ $text !== '' ? $text : "\u{00A0}" }}</div>
                                    @endforeach
                                @endforeach
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </table>
        @elseif ($block['type'] === 'paragraph')
            <p @if ($block['bold']) style="font-weight: bold" @endif>{{ $block['text'] }}</p>
        @elseif ($block['type'] === 'bullets')
            <ul>
                @foreach ($block['items'] as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        @endif
    @endforeach
</body>
</html>
