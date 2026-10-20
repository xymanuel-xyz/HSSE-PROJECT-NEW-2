@extends('exports.layout')

@section('content')
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 4%;" class="text-center">No</th>
            <th style="width: 11%;">Kode Temuan</th>
            <th style="width: 9%;" class="text-center">Tgl Temuan</th>
            <th style="width: 12%;">Fungsi Safeguard</th>
            <th style="width: 13%;">Sumber Temuan</th>
            <th style="width: 23%;">Judul &amp; Deskripsi Temuan</th>
            <th style="width: 7%;" class="text-center">Status</th>
            <th style="width: 8%;" class="text-center">Tgl Close</th>
            <th style="width: 13%;" class="text-center">Dokumentasi Foto</th>
        </tr>
    </thead>
    <tbody>
        @forelse($records as $index => $item)
            @php
                $isClose = in_array($item->status, ['Close', 'Closed'], true);
                $statClass = $isClose ? 'badge-success' : 'badge-warning';
                $statusDisplay = $isClose ? 'Close' : 'Open';
                
                // Ambil foto dokumentasi pertama untuk preview embedded base64
                $photoPath = null;
                $docFiles = $item->documentations ?? collect();
                $firstDoc = $docFiles->first();

                if ($firstDoc && $firstDoc->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($firstDoc->file_path)) {
                    $photoPath = \Illuminate\Support\Facades\Storage::disk('public')->path($firstDoc->file_path);
                } elseif (!empty($item->attachment_path) && \Illuminate\Support\Facades\Storage::disk('public')->exists($item->attachment_path)) {
                    $photoPath = \Illuminate\Support\Facades\Storage::disk('public')->path($item->attachment_path);
                }

                $docCount = $docFiles->count();
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td style="font-family: monospace; font-weight: bold;">{{ $item->issue_code ?? '-' }}</td>
                <td class="text-center">{{ $item->reported_date ? $item->reported_date->format('d/m/Y') : '-' }}</td>
                <td class="text-bold">{{ $item->function_label ?? $item->hsse_division ?? '-' }}</td>
                <td>{{ $item->source_label ?? $item->category ?? '-' }}</td>
                <td>
                    <div class="text-bold">{{ $item->title }}</div>
                    @if($item->description)
                        <div style="font-size: 7.5pt; color: #475569; margin-top: 2px;">
                            {{ \Illuminate\Support\Str::limit($item->description, 110) }}
                        </div>
                    @endif
                </td>
                <td class="text-center">
                    <span class="badge {{ $statClass }}">{{ $statusDisplay }}</span>
                </td>
                <td class="text-center">
                    @if($isClose && $item->closed_date)
                        {{ $item->closed_date->format('d/m/Y') }}
                    @elseif($isClose && $item->due_date)
                        {{ $item->due_date->format('d/m/Y') }}
                    @else
                        -
                    @endif
                </td>
                <td class="text-center" style="vertical-align: middle;">
                    @if($photoPath && file_exists($photoPath))
                        @php
                            $ext = strtolower(pathinfo($photoPath, PATHINFO_EXTENSION));
                            $mime = match($ext) {
                                'png' => 'image/png',
                                'webp' => 'image/webp',
                                'gif' => 'image/gif',
                                default => 'image/jpeg',
                            };
                            $base64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($photoPath));
                        @endphp
                        <div style="margin: 2px 0;">
                            <img src="{{ $base64 }}" style="max-width: 85px; max-height: 55px; border-radius: 4px; border: 1px solid #cbd5e1; display: inline-block;">
                        </div>
                        @if($docCount > 1)
                            <div style="font-size: 6.5pt; color: #64748b; margin-top: 2px;">+{{ $docCount - 1 }} foto lain</div>
                        @endif
                    @elseif($docCount > 0)
                        <span class="badge badge-info">{{ $docCount }} File</span>
                    @else
                        <span style="color: #94a3b8;">-</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="text-center" style="padding: 24px; color: #94a3b8;">
                    Tidak ada rekaman data temuan Safeguard yang sesuai dengan filter fungsi atau status ini.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
