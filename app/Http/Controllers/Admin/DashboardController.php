<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use App\Models\Disposisi;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today();

        // Stat cards
        $suratMasukHariIni = SuratMasuk::whereDate('created_at', $today)->count();
        $suratKeluarHariIni = SuratKeluar::whereDate('created_at', $today)->count();

        // Data disposisi berdasarkan jabatan
        $belumDisposisi = 0;
        $prioritasTinggi = 0;
        $disposisiTerbaru = collect();
        $disposisiSaya = 0;

        // Kepala Sekolah bisa melihat seluruh disposisi
        if ($user->jabatan === 'Kepala Sekolah') {
            $belumDisposisi = Disposisi::where('status', 'pending')->count();

            $prioritasTinggi = Disposisi::whereIn('sifat', [
                'sangat_segera',
                'rahasia'
            ])
                ->where('status', '!=', 'selesai')
                ->count();

            $disposisiTerbaru = Disposisi::with([
                'suratMasuk',
                'tujuan'
            ])
                ->latest()
                ->take(5)
                ->get();
        } else {
            // User lain hanya melihat disposisi yang ditujukan kepadanya
            $disposisiSaya = Disposisi::where(
                'tujuan_user_id',
                $user->id
            )->count();

            $belumDisposisi = Disposisi::where(
                'tujuan_user_id',
                $user->id
            )
                ->where('status', 'pending')
                ->count();

            $prioritasTinggi = Disposisi::where(
                'tujuan_user_id',
                $user->id
            )
                ->whereIn('sifat', [
                    'sangat_segera',
                    'rahasia'
                ])
                ->where('status', '!=', 'selesai')
                ->count();

            $disposisiTerbaru = Disposisi::with('suratMasuk')
                ->where('tujuan_user_id', $user->id)
                ->latest()
                ->take(5)
                ->get();
        }

        // Chart: surat masuk vs keluar, 6 bulan terakhir
        $bulanLabel = [];
        $dataMasuk = [];
        $dataKeluar = [];

        for ($i = 5; $i >= 0; $i--) {
            $bulan = Carbon::now()->subMonths($i);

            $bulanLabel[] = $bulan->translatedFormat('M');

            $dataMasuk[] = SuratMasuk::whereMonth(
                'created_at',
                $bulan->month
            )
                ->whereYear(
                    'created_at',
                    $bulan->year
                )
                ->count();

            $dataKeluar[] = SuratKeluar::whereMonth(
                'created_at',
                $bulan->month
            )
                ->whereYear(
                    'created_at',
                    $bulan->year
                )
                ->count();
        }

        // Surat terbaru
        $suratMasuk = SuratMasuk::latest()
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'tipe'        => 'Masuk',
                    'nomor_surat' => $item->nomor_surat,
                    'pihak'       => $item->pengirim,
                    'perihal'     => $item->perihal,
                    'tanggal'     => $item->tanggal_surat,
                    'status'      => 'Belum Disposisi',
                ];
            });

        $suratKeluar = SuratKeluar::latest()
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'tipe'        => 'Keluar',
                    'nomor_surat' => $item->nomor_surat,
                    'pihak'       => $item->tujuan,
                    'perihal'     => $item->perihal,
                    'tanggal'     => $item->tanggal_surat,
                    'status'      => ucfirst($item->status),
                ];
            });

        $suratTerbaru = $suratMasuk
            ->concat($suratKeluar)
            ->sortByDesc('tanggal')
            ->take(8)
            ->values();

        // Laporan rekap (hanya Kepala Sekolah & Tata Usaha)
        $bisaLihatLaporan = in_array($user->jabatan, ['Kepala Sekolah', 'Tata Usaha'], true);
        [$dari, $sampai] = $this->periodeLaporan($request);

        $laporan = $bisaLihatLaporan
            ? $this->rekapLaporan($dari, $sampai)
            : null;

        return view('admin.dashboard.index', [
            'laporan' => $laporan,
            'laporanDari' => $dari,
            'laporanSampai' => $sampai,

            'title' => 'Dashboard',
            'subtitle' => 'Ringkasan aktivitas persuratan dan sekolah hari ini.',

            'suratHariIni' => $suratMasukHariIni + $suratKeluarHariIni,
            'masukHariIni' => $suratMasukHariIni,
            'keluarHariIni' => $suratKeluarHariIni,

            'belumDisposisi' => $belumDisposisi,
            'prioritasTinggi' => $prioritasTinggi,
            'disposisiSaya' => $disposisiSaya,

            'chartLabel' => $bulanLabel,
            'chartMasuk' => $dataMasuk,
            'chartKeluar' => $dataKeluar,

            'disposisiTerbaru' => $disposisiTerbaru,
            'suratTerbaru' => $suratTerbaru,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | LAPORAN REKAP — CETAK PDF
    |--------------------------------------------------------------------------
    */
    public function laporanPdf(Request $request)
    {
        abort_unless(
            in_array(auth()->user()->jabatan, ['Kepala Sekolah', 'Tata Usaha'], true),
            403
        );

        [$dari, $sampai] = $this->periodeLaporan($request);

        $laporan = $this->rekapLaporan($dari, $sampai, true);

        $pdf = Pdf::loadView('admin.dashboard.laporan-pdf', [
            'laporan' => $laporan,
            'dari'    => $dari,
            'sampai'  => $sampai,
            'sekolah' => \App\Models\Sekolah::first(),
            'dicetak' => auth()->user(),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream(
            'laporan-surat-' . $dari->format('Ymd') . '-' . $sampai->format('Ymd') . '.pdf'
        );
    }

    /*
    | Ambil periode dari query string (?dari=YYYY-MM-DD&sampai=YYYY-MM-DD).
    | Default: awal bulan ini s.d. hari ini.
    */
    private function periodeLaporan(Request $request): array
    {
        try {
            $dari = $request->filled('dari')
                ? Carbon::parse($request->input('dari'))->startOfDay()
                : Carbon::now()->startOfMonth();

            $sampai = $request->filled('sampai')
                ? Carbon::parse($request->input('sampai'))->endOfDay()
                : Carbon::now()->endOfDay();
        } catch (\Exception $e) {
            $dari = Carbon::now()->startOfMonth();
            $sampai = Carbon::now()->endOfDay();
        }

        if ($dari->gt($sampai)) {
            [$dari, $sampai] = [$sampai->copy()->startOfDay(), $dari->copy()->endOfDay()];
        }

        return [$dari, $sampai];
    }

    /*
    | Hitung rekap surat masuk & keluar dalam periode.
    | Surat masuk pakai tanggal_terima, surat keluar pakai tanggal_surat.
    */
    private function rekapLaporan(Carbon $dari, Carbon $sampai, bool $denganDaftar = false): array
    {
        $suratMasuk = SuratMasuk::with('disposisi')
            ->whereBetween('tanggal_terima', [$dari->toDateString(), $sampai->toDateString()])
            ->orderBy('tanggal_terima')
            ->get();

        $suratKeluar = SuratKeluar::whereBetween('tanggal_surat', [$dari->toDateString(), $sampai->toDateString()])
            ->orderBy('tanggal_surat')
            ->get();

        // Status surat masuk diambil dari disposisi terakhirnya
        $statusMasuk = function ($surat) {
            $terakhir = $surat->disposisi->sortByDesc('id')->first();

            return match ($terakhir->status ?? null) {
                'selesai'                => 'Selesai',
                'diproses', 'diteruskan' => 'Diproses',
                default                  => 'Menunggu Disposisi',
            };
        };

        $masukPerStatus = [
            'Menunggu Disposisi' => 0,
            'Diproses'           => 0,
            'Selesai'            => 0,
        ];

        $prioritasTinggi = 0;

        foreach ($suratMasuk as $surat) {
            $masukPerStatus[$statusMasuk($surat)]++;

            $sifat = $surat->disposisi->sortByDesc('id')->first()->sifat ?? null;

            if (in_array($sifat, ['sangat_segera', 'rahasia'], true)) {
                $prioritasTinggi++;
            }
        }

        $labelKeluar = [
            'draft'            => 'Draft',
            'menunggu_approve' => 'Menunggu Persetujuan',
            'terbit'           => 'Disetujui',
        ];

        $keluarPerStatus = array_fill_keys(array_values($labelKeluar), 0);

        foreach ($suratKeluar as $surat) {
            $label = $labelKeluar[$surat->status] ?? 'Draft';
            $keluarPerStatus[$label]++;
        }

        // Rekap per bulan di dalam periode
        $perBulan = [];
        $bulan = $dari->copy()->startOfMonth();

        while ($bulan->lte($sampai) && count($perBulan) < 36) {
            $kunci = $bulan->format('Y-m');

            $perBulan[$kunci] = [
                'label'  => $bulan->translatedFormat('F Y'),
                'masuk'  => $suratMasuk->filter(fn ($s) => Carbon::parse($s->tanggal_terima)->format('Y-m') === $kunci)->count(),
                'keluar' => $suratKeluar->filter(fn ($s) => Carbon::parse($s->tanggal_surat)->format('Y-m') === $kunci)->count(),
            ];

            $bulan->addMonth();
        }

        // Pengirim surat masuk terbanyak
        $topPengirim = $suratMasuk
            ->groupBy(fn ($s) => trim($s->pengirim))
            ->map->count()
            ->sortDesc()
            ->take(5);

        $totalMasuk = $suratMasuk->count();

        $hasil = [
            'total_masuk'       => $totalMasuk,
            'total_keluar'      => $suratKeluar->count(),
            'masuk_per_status'  => $masukPerStatus,
            'keluar_per_status' => $keluarPerStatus,
            'prioritas_tinggi'  => $prioritasTinggi,
            'persen_selesai'    => $totalMasuk > 0
                ? round($masukPerStatus['Selesai'] / $totalMasuk * 100)
                : 0,
            'per_bulan'         => array_values($perBulan),
            'top_pengirim'      => $topPengirim,
        ];

        if ($denganDaftar) {
            $hasil['daftar_masuk'] = $suratMasuk->map(fn ($s) => [
                'tanggal' => $s->tanggal_terima,
                'no_agenda' => $s->no_agenda,
                'nomor'   => $s->nomor_surat,
                'pihak'   => $s->pengirim,
                'perihal' => $s->perihal,
                'status'  => $statusMasuk($s),
            ]);

            $hasil['daftar_keluar'] = $suratKeluar->map(fn ($s) => [
                'tanggal' => $s->tanggal_surat,
                'nomor'   => $s->nomor_surat,
                'pihak'   => $s->tujuan,
                'perihal' => $s->perihal,
                'status'  => $labelKeluar[$s->status] ?? '-',
            ]);
        }

        return $hasil;
    }
}