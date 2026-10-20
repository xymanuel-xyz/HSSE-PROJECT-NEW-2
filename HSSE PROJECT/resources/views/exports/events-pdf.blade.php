@extends('exports.layout')

@section('content')
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 4%;" class="text-center">No</th>
            <th style="width: 20%;">Judul Kegiatan</th>
            <th style="width: 15%;">Nama Perusahaan / Instansi</th>
            <th style="width: 9%;" class="text-center">Tanggal</th>
            <th style="width: 8%;" class="text-center">Status</th>
            <th style="width: 24%;">Deskripsi Kegiatan</th>
            <th style="width: 20%;" class="text-center">Dokumentasi Foto</th>
        </tr>
    </thead>
    <tbody>
        @forelse($records as $index => $item)
            @php
                $badgeClass = match($item->status) {
                    'Selesai' => 'badge-success',
                    'Berlangsung' => 'badge-warning',
                    'Dibatalkan' => 'badge-danger',
                    default => 'badge-secondary',
                };

                // Ambil file foto dokumentasi
                $photoPath = null;
                $docFiles = $item->documents ?? collect();
                $firstDoc = $docFiles->first();

                if ($firstDoc && $firstDoc->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($firstDoc->file_path)) {
                    $photoPath = \Illuminate\Support\Facades\Storage::disk('public')->path($firstDoc->file_path);
                } elseif (!empty($item->documentation) && \Illuminate\Support\Facades\Storage::disk('public')->exists($item->documentation)) {
                    $photoPath = \Illuminate\Support\Facades\Storage::disk('public')->path($item->documentation);
                }

                $docCount = $docFiles->count();
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-bold">{{ $item->title }}</td>
                <td>{{ $item->company ?? '-' }}</td>
                <td class="text-center">{{ $item->event_date ? $item->event_date->format('d/m/Y') : '-' }}</td>
                <td class="text-center"><span class="badge {{ $badgeClass }}">{{ $item->status }}</span></td>
                <td>
                    <div style="font-size: 7.5pt; color: #334155;">
                        {{ $item->description ?: '-' }}
                    </div>
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
                            <img src="{{ $base64 }}" style="max-width: 95px; max-height: 65px; border-radius: 4px; border: 1px solid #cbd5e1; display: inline-block;">
                        </div>
                        @if($docCount > 1)
                            <div style="font-size: 7pt; color: #64748b; margin-top: 2px;">+{{ $docCount - 1 }} foto lainnya</div>
                        @endif
                    @elseif($docCount > 0)
                        <div style="font-size: 7.5pt; color: #0284c7; font-weight: bold;">
                            {{ $docCount }} File Dokumen
                        </div>
                    @else
                        <span style="color: #94a3b8; font-size: 7.5pt; font-style: italic;">Tidak ada foto</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: 20px; color: #94a3b8;">
                    Tidak ada data kegiatan events yang sesuai dengan kriteria filter.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection

