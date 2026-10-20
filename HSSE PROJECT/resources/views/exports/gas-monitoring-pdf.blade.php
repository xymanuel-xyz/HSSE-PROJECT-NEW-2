@extends('exports.layout')

@section('content')
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 4%;" class="text-center">No</th>
            <th style="width: 10%;" class="text-center">Tanggal</th>
            <th style="width: 13%;">Well Pad</th>
            <th style="width: 8%;" class="text-right">CO (ppm)</th>
            <th style="width: 9%;" class="text-center">Status CO</th>
            <th style="width: 8%;" class="text-right">H₂S (ppm)</th>
            <th style="width: 9%;" class="text-center">Status H₂S</th>
            <th style="width: 9%;" class="text-right">Press (barg)</th>
            <th style="width: 10%;" class="text-center">Status Press</th>
            <th style="width: 10%;">Petugas</th>
            <th style="width: 10%;">Catatan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($records as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $item->measurement_date ? $item->measurement_date->format('d/m/Y') : '-' }}</td>
                <td class="text-bold">{{ $item->well_pad }}</td>
                <td class="text-right">{{ number_format($item->co_level, 0) }}</td>
                <td class="text-center">
                    <span class="badge {{ $item->co_status === 'Sesuai' ? 'badge-success' : 'badge-danger' }}">
                        {{ $item->co_status }}
                    </span>
                </td>
                <td class="text-right">{{ number_format($item->h2s_level, 0) }}</td>
                <td class="text-center">
                    <span class="badge {{ $item->h2s_status === 'Sesuai' ? 'badge-success' : 'badge-danger' }}">
                        {{ $item->h2s_status }}
                    </span>
                </td>
                <td class="text-right">{{ number_format($item->pressure, 1) }}</td>
                <td class="text-center">
                    <span class="badge {{ $item->pressure_status === 'Sesuai' ? 'badge-success' : 'badge-danger' }}">
                        {{ $item->pressure_status }}
                    </span>
                </td>
                <td>{{ $item->officer ?? '-' }}</td>
                <td>{{ $item->notes ?? '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="11" class="text-center" style="padding: 20px; color: #94a3b8;">
                    Tidak ada data pemantauan gas yang sesuai dengan kriteria filter.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
