<table>
    <thead>
        <tr>
            <td colspan="8"><strong>{{ $communityName }} — Rekap Laporan Proker</strong></td>
        </tr>
        <tr>
            <td colspan="8">Periode: {{ $period }} · Diunduh {{ now()->format('d M Y H:i') }}</td>
        </tr>
        <tr></tr>
        <tr>
            <th>No</th>
            <th>Judul</th>
            <th>Kategori</th>
            <th>Tanggal</th>
            <th>Lokasi</th>
            <th>Pelapor</th>
            <th>Deskripsi</th>
            <th>Dibuat pada</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($reports as $i => $r)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $r->title }}</td>
                <td>{{ $r->category?->name ?? '-' }}</td>
                <td>{{ $r->start_date->format('d M Y') }}{{ $r->end_date && $r->end_date->format('Y-m-d') !== $r->start_date->format('Y-m-d') ? ' – '.$r->end_date->format('d M Y') : '' }}</td>
                <td>{{ $r->location ?? '-' }}</td>
                <td>{{ $r->user?->name ?? '-' }}</td>
                <td>{{ \Illuminate\Support\Str::limit(strip_tags($r->description), 200) }}</td>
                <td>{{ $r->created_at->format('d M Y H:i') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>