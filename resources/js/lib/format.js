/**
 * Format tanggal/pilihan bahasa Indonesia untuk seluruh aplikasi.
 *
 * Dipusatkan di sini karena sebelumnya setiap halaman memanggil
 * `toLocaleDateString('en-GB', …)` sendiri-sendiri, sehingga tanggal tampil
 * dalam nama bulan bahasa Inggris ("Sep", "Aug") di aplikasi berbahasa Indonesia.
 */

const TANGGAL_PANJANG = {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
};

const TANGGAL_SINGKAT = { day: 'numeric', month: 'short', year: 'numeric' };

/** "2026-09-13" → "13 Sep 2026". Nilai kosong → "—". */
export function formatTanggal(value, opts = TANGGAL_SINGKAT) {
    if (!value) return '—';
    const d = new Date(value);
    if (Number.isNaN(d.getTime())) return '—';
    return d.toLocaleDateString('id-ID', opts);
}

/** "2026-09-13" → "Minggu, 13 September 2026". */
export function formatTanggalPenuh(value) {
    return formatTanggal(value, TANGGAL_PANJANG);
}

/**
 * Rentang tanggal: satu tanggal jika mulai = selesai (atau selesai kosong),
 * selain itu "mulai – selesai". Bulan/tahun yang sama diringkas menjadi
 * "12–14 Sep 2026" agar hemat ruang di tabel.
 */
export function formatRentang(start, end) {
    if (!start) return '—';
    if (!end || end === start) return formatTanggal(start);

    const a = new Date(start);
    const b = new Date(end);
    if (Number.isNaN(a.getTime()) || Number.isNaN(b.getTime())) return formatTanggal(start);

    const samaBulanTahun =
        a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth();

    if (samaBulanTahun) {
        return `${a.getDate()}–${b.getDate()} ${formatTanggal(start).replace(/^\d+\s/, '')}`;
    }
    return `${formatTanggal(start)} – ${formatTanggal(end)}`;
}

/** Inisial nama untuk avatar: "Admin FAD" → "AF". Maks 2 huruf. */
export function inisial(nama) {
    const bagian = (nama ?? '').trim().split(/\s+/).filter(Boolean);
    if (!bagian.length) return '?';
    if (bagian.length === 1) return bagian[0].slice(0, 2).toUpperCase();
    return (bagian[0][0] + bagian[bagian.length - 1][0]).toUpperCase();
}
