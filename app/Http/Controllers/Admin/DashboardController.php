<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use App\Models\Disposisi;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
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

        return view('admin.dashboard.index', [
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
}
