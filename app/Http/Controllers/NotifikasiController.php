<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Yajra\DataTables\DataTables;

class NotifikasiController extends Controller
{

    public function show(Request $request)
    {
        $user = session('user');
        if (!$user) {
            return redirect()->route('login')->withErrors(['login_error' => 'Please log in first.']);
        }

        // Set tahun dan bulan, gunakan variabel agar mudah diubah
        $tahun = 2025;
        // Ambil bulan dari request, default ke 4 (April) jika tidak ada
        $bulan = request('bulan', 4);
        // Validasi agar hanya 1-12 yang diterima
        if (!in_array((int)$bulan, range(1, 12))) {
            $bulan = 4;
        }

        $bpadmasterData = DB::connection('sqlsrv')->table('master_profile')
            ->join('master_profile_detail', 'master_profile.id_kolok', '=', 'master_profile_detail.id_kolok')
            ->where('master_profile_detail.tahun', $tahun)
            ->whereNull('master_profile_detail.upb_sekolah') // Pastikan UPB Sekolah NULL
            ->whereNull('master_profile_detail.flag_blud') // Pastikan Flag BLUD NULL
            ->where('master_profile_detail.sts', '1')
            ->select(
                'master_profile.id_kolok',
                'master_profile.nalok',
                'master_profile_detail.tahun',
                'master_profile_detail.upb_sekolah',
                'master_profile_detail.flag_blud'
            )
            ->get()
            ->keyBy('id_kolok');

        $bpadinventoryData = DB::connection('sqlsrv_2')->table('so_data2025')
            ->whereYear('periode_baso', $tahun)
            ->whereMonth('periode_baso', $bulan)
            ->select('kolok', 'smt', 'periode_baso', 'tglba_fisik', 'no_bafisik')
            ->get()
            ->keyBy('kolok');


        // Ambil jumlah data rekon dari sqlsrv_3 (rekon_bku) berdasarkan tahun dan bulan <= bulan yang dipilih
        $rekonBku = DB::connection('sqlsrv_3')
            ->table('rekon_bku')
            ->select('id_kolok', DB::raw('COUNT(*) as jumlah_rekon'))
            ->whereYear('tgl_post', $tahun)
            ->whereBetween(DB::raw('MONTH(tgl_post)'), [1, $bulan])
            ->groupBy('id_kolok')
            ->pluck('jumlah_rekon', 'id_kolok');

        // Ambil jumlah data belum direkon dari sqlsrv_3 (rekon_bku_belum) berdasarkan tahun dan bulan <= bulan yang dipilih
        $rekonBkuBelum = DB::connection('sqlsrv_3')
            ->table('rekon_bku_belum')
            ->select('id_kolok', DB::raw('COUNT(*) as jumlah_belum_rekon'))
            ->whereYear('tgl_post', $tahun)
            ->whereBetween(DB::raw('MONTH(tgl_post)'), [1, $bulan])
            ->groupBy('id_kolok')
            ->pluck('jumlah_belum_rekon', 'id_kolok');

        // Gabungkan semua data
        $mergedData = $bpadmasterData->map(function ($master) use ($bpadinventoryData, $rekonBku, $rekonBkuBelum) {
            $inventory = $bpadinventoryData[$master->id_kolok] ?? null;

            $master->smt = $inventory->smt ?? 'No Data Found';
            $master->periode_baso = $inventory->periode_baso ?? 'No Data Found';
            $master->tglba_fisik = $inventory->tglba_fisik ?? 'No Data Found';
            $master->no_bafisik = $inventory->no_bafisik ?? 'No Data Found';

            // Tambahkan jumlah rekon dari rekon_bku
            $master->jumlah_rekon = $rekonBku[$master->id_kolok] ?? 0;

            // Tambahkan jumlah belum rekon dari rekon_bku_belum
            $master->jumlah_belum_rekon = $rekonBkuBelum[$master->id_kolok] ?? 0;

            return $master;
        });


        // Helper untuk query count per kolok/idskpd, hasil di-keyBy agar lookup cepat
        $countBy = function ($connection, $table, $groupCol, $where = []) {
            $query = DB::connection($connection)->table($table)->select($groupCol, DB::raw('COUNT(*) as jumlah'));
            foreach ($where as $w) {
                $query->whereRaw($w[0], $w[1] ?? []);
            }
            return $query->groupBy($groupCol)->get()->keyBy($groupCol);
        };

        // SPPB & BASTSPPB
        $sppbFilters = [
            ['SUBSTRING(LTRIM(RTRIM(noref)), 8, 5) = ?', ['RQ2.1']],
            ['SUBSTRING(LTRIM(RTRIM(noref)), 8, 5) = ?', ['RQ2.3']],
            ['SUBSTRING(LTRIM(RTRIM(noref)), 8, 5) = ?', ['RQ2.4']],
            ['SUBSTRING(LTRIM(RTRIM(noref)), 8, 5) = ?', ['RQ2.5']],
        ];
        $bastFilters = $sppbFilters;

        $sppb = [];
        $bastSPPB = [];
        foreach ($sppbFilters as $i => $filter) {
            $sppb[$i] = $countBy('sqlsrv_2', 'rq_data2025', 'idskpd', [
                $filter,
                ['YEAR(tgl_rq) = ?', [$tahun]],
                ['MONTH(tgl_rq) = ?', [$bulan]],
                ["(stat_rq IS NULL OR stat_rq = '')"],
                ["sts = 1"]
            ]);
            $bastSPPB[$i] = $countBy('sqlsrv_2', 'rq_data2025', 'idskpd', [
                $filter,
                ['YEAR(tgl_rq) = ?', [$tahun]],
                ['MONTH(tgl_rq) = ?', [$bulan]],
                ["stat_rq = 1 AND stat_form IS NULL"],
                ["sts = 1"]
            ]);
        }

        // BASTPHK3, BASTHIBAH
        $bastPHK3 = $countBy('sqlsrv_2', 'bast_data2025', 'kolok', [
            ["tipe_bast = 1"],
            ['YEAR(tgl_bast) = ?', [$tahun]],
            ['MONTH(tgl_bast) = ?', [$bulan]],
            ["(stat_bast IS NULL OR stat_bast = '')"],
            ["sts = 1"]
        ]);
        $bastHIBAH = $countBy('sqlsrv_2', 'bast_data2025', 'kolok', [
            ["tipe_bast = 2"],
            ['YEAR(tgl_bast) = ?', [$tahun]],
            ['MONTH(tgl_bast) = ?', [$bulan]],
            ["(stat_bast IS NULL OR stat_bast = '')"],
            ["sts = 1"]
        ]);

        // BASTTRANSFER
        $bastTRANSFER = $countBy('sqlsrv_2', 'rq_data2025', 'idkolok', [
            ["SUBSTRING(noref, 10, 3) = '2.6'"],
            ['YEAR(tgl_rq) = ?', [$tahun]],
            ['MONTH(tgl_rq) = ?', [$bulan]],
            ["stat_rq = 1 AND stat_form IS NULL"],
            ["sts = 1"]
        ]);

        // Tambah/Kurang
        $reviewTambah = $countBy('sqlsrv_2', 'bast_data2025', 'kolok', [
            ["tipe_bast = 7"],
            ['YEAR(tgl_bast) = ?', [$tahun]],
            ['MONTH(tgl_bast) = ?', [$bulan]],
            ["(stat_bast IS NULL OR stat_bast = '')"],
            ["sts = 1"]
        ]);
        $instTambah = $countBy('sqlsrv_2', 'bast_data2025', 'kolok', [
            ["tipe_bast = 5"],
            ['YEAR(tgl_bast) = ?', [$tahun]],
            ['MONTH(tgl_bast) = ?', [$bulan]],
            ["(stat_bast IS NULL OR stat_bast = '')"],
            ["sts = 1"]
        ]);
        $auditTambah = $countBy('sqlsrv_2', 'bast_data2025', 'kolok', [
            ["tipe_bast = 6"],
            ['YEAR(tgl_bast) = ?', [$tahun]],
            ['MONTH(tgl_bast) = ?', [$bulan]],
            ["(stat_bast IS NULL OR stat_bast = '')"],
            ["sts = 1"]
        ]);
        $reviewKurang = $countBy('sqlsrv_2', 'rq_data2025', 'idkolok', [
            ["SUBSTRING(noref, 10, 3) = '4.6'"],
            ['YEAR(tgl_rq) = ?', [$tahun]],
            ['MONTH(tgl_rq) = ?', [$bulan]],
            ["stat_rq = 1 AND stat_form IS NULL"],
            ["sts = 1"]
        ]);
        $insKurang = $countBy('sqlsrv_2', 'rq_data2025', 'idkolok', [
            ["SUBSTRING(noref, 10, 3) = '4.2'"],
            ['YEAR(tgl_rq) = ?', [$tahun]],
            ['MONTH(tgl_rq) = ?', [$bulan]],
            ["stat_rq = 1 AND stat_form IS NULL"],
            ["sts = 1"]
        ]);
        $auditKurang = $countBy('sqlsrv_2', 'rq_data2025', 'idkolok', [
            ["SUBSTRING(noref, 10, 3) = '4.4'"],
            ['YEAR(tgl_rq) = ?', [$tahun]],
            ['MONTH(tgl_rq) = ?', [$bulan]],
            ["stat_rq = 1 AND stat_form IS NULL"],
            ["sts = 1"]
        ]);

        // Semester
        $smt1Tambah = $countBy('sqlsrv_2', 'bast_data2025', 'kolok', [
            ["tipe_bast = '+'"],
            ["(stat_bast IS NULL OR stat_bast = '')"],
            ["sts = 1"],
            ["MONTH(tgl_bast) < 7"]
        ]);
        $smt2Tambah = $countBy('sqlsrv_2', 'bast_data2025', 'kolok', [
            ["tipe_bast = '+'"],
            ["(stat_bast IS NULL OR stat_bast = '')"],
            ["sts = 1"],
            ["MONTH(tgl_bast) > 6"]
        ]);
        $smt1Kurang = $countBy('sqlsrv_2', 'rq_data2025', 'idkolok', [
            ["SUBSTRING(noref, 8, 4) = 'KR2.'"],
            ['YEAR(tgl_rq) = ?', [$tahun]],
            ["stat_rq = 1 AND stat_form IS NULL"],
            ["sts = 1"],
            ["MONTH(tgl_rq) < 7"]
        ]);
        $smt2Kurang = $countBy('sqlsrv_2', 'rq_data2025', 'idkolok', [
            ["SUBSTRING(noref, 8, 4) = 'KR2.'"],
            ['YEAR(tgl_rq) = ?', [$tahun]],
            ["stat_rq = 1 AND stat_form IS NULL"],
            ["sts = 1"],
            ["MONTH(tgl_rq) > 6"]
        ]);

        // Gabungkan semua data ke mergedData
        $mergedData = $mergedData->map(function ($item) use (
            $sppb, $bastSPPB, $bastPHK3, $bastHIBAH, $bastTRANSFER,
            $reviewTambah, $instTambah, $auditTambah, $smt1Tambah, $smt2Tambah,
            $reviewKurang, $insKurang, $auditKurang, $smt1Kurang, $smt2Kurang,
        ) {
            $id = $item->id_kolok;
            $item->SPPB1 = $sppb[0][$id]->jumlah ?? 0;
            $item->SPPB2 = $sppb[1][$id]->jumlah ?? 0;
            $item->SPPB3 = $sppb[2][$id]->jumlah ?? 0;
            $item->SPPB4 = $sppb[3][$id]->jumlah ?? 0;
            $item->BASTSPPB1 = $bastSPPB[0][$id]->jumlah ?? 0;
            $item->BASTSPPB2 = $bastSPPB[1][$id]->jumlah ?? 0;
            $item->BASTSPPB3 = $bastSPPB[2][$id]->jumlah ?? 0;
            $item->BASTSPPB4 = $bastSPPB[3][$id]->jumlah ?? 0;
            $item->BASTPHK3 = $bastPHK3[$id]->jumlah ?? 0;
            $item->BASTHIBAH = $bastHIBAH[$id]->jumlah ?? 0;
            $item->BASTTRANSFER = $bastTRANSFER[$id]->jumlah ?? 0;
            $item->REVIEWTAMBAH = $reviewTambah[$id]->jumlah ?? 0;
            $item->INSTAMBAH = $instTambah[$id]->jumlah ?? 0;
            $item->AUDITTAMBAH = $auditTambah[$id]->jumlah ?? 0;
            $item->SMT1TAMBAH = $smt1Tambah[$id]->jumlah ?? 0;
            $item->SMT2TAMBAH = $smt2Tambah[$id]->jumlah ?? 0;
            $item->REVIEWKURANG = $reviewKurang[$id]->jumlah ?? 0;
            $item->INSKURANG = $insKurang[$id]->jumlah ?? 0;
            $item->AUDITKURANG = $auditKurang[$id]->jumlah ?? 0;
            $item->SMT1KURANG = $smt1Kurang[$id]->jumlah ?? 0;
            $item->SMT2KURANG = $smt2Kurang[$id]->jumlah ?? 0;
            $item->Total_SPPB_BAST =
                $item->SPPB1 + $item->SPPB2 + $item->SPPB3 + $item->SPPB4 +
                $item->BASTSPPB1 + $item->BASTSPPB2 + $item->BASTSPPB3 + $item->BASTSPPB4 +
                $item->BASTPHK3 + $item->BASTHIBAH + $item->BASTTRANSFER +
                $item->REVIEWTAMBAH + $item->INSTAMBAH + $item->AUDITTAMBAH +
                $item->SMT1TAMBAH + $item->SMT2TAMBAH +
                $item->REVIEWKURANG + $item->INSKURANG + $item->AUDITKURANG +
                $item->SMT1KURANG + $item->SMT2KURANG;
            return $item;
        });

        // Hitung selesai/belum
        $selesaiCount = $belumCount = 0;
        foreach ($mergedData as $item) {
            $jumlahRekon = $item->jumlah_rekon ?? 0;
            $jumlahBelumRekon = $item->jumlah_belum_rekon ?? 0;

            if ($item->upb_sekolah !== 'Y' && $item->flag_blud !== 'Y') {
                if (
                    ($item->Total_SPPB_BAST == 0) &&
                    ($item->tglba_fisik !== 'No Data Found' && !is_null($item->tglba_fisik)) &&
                    ($item->periode_baso !== 'No Data Found' && !is_null($item->periode_baso)) &&
                    ($jumlahBelumRekon == 0 && $jumlahRekon >= 0)
                ) {
                    $selesaiCount++;
                } else {
                    $belumCount++;
                }
            }
        }

        return view('Backend.persediaanpdopd', [
            'mergedData' => $mergedData,
            'bulan' => $bulan,
            'selesaiCount' => $selesaiCount,
            'belumCount' => $belumCount,
            'user' => $user,
        ]);
    }

    public function showSekolah(Request $request)
    {
        $user = session('user');
        if (!$user) {
            return redirect()->route('login')->withErrors(['login_error' => 'Please log in first.']);
        }

        // Set tahun dan bulan, gunakan variabel agar mudah diubah
        $tahun = 2025;
        // Ambil bulan dari request, default ke 4 (April) jika tidak ada
        $bulan = request('bulan', 4);
        // Validasi agar hanya 1-12 yang diterima
        if (!in_array((int)$bulan, range(1, 12))) {
            $bulan = 4;
        }

        // Ambil master data dan inventory data sekaligus, hanya kolom yang diperlukan
        $bpadmasterData = DB::connection('sqlsrv')->table('master_profile')
            ->join('master_profile_detail', 'master_profile.id_kolok', '=', 'master_profile_detail.id_kolok')
            ->where('master_profile_detail.tahun', $tahun)
            ->where('master_profile_detail.upb_sekolah', 'Y')
            ->where('master_profile_detail.sts', '1')
            ->select(
                'master_profile.id_kolok',
                'master_profile.nalok',
                'master_profile_detail.tahun',
                'master_profile_detail.upb_sekolah',
                'master_profile_detail.flag_blud'
            )
            ->get()
            ->keyBy('id_kolok');

        $bpadinventoryData = DB::connection('sqlsrv_2')->table('so_data2025')
            ->whereYear('periode_baso', $tahun)
            ->whereMonth('periode_baso', $bulan)
            ->select('kolok', 'smt', 'periode_baso', 'tglba_fisik', 'no_bafisik')
            ->get()
            ->keyBy('kolok');


        // Ambil jumlah data rekon dari sqlsrv_3 (rekon_bku) berdasarkan tahun dan bulan <= bulan yang dipilih
        $rekonBku = DB::connection('sqlsrv_3')
            ->table('rekon_bku')
            ->select('id_kolok', DB::raw('COUNT(*) as jumlah_rekon'))
            ->whereYear('tgl_post', $tahun)
            ->whereBetween(DB::raw('MONTH(tgl_post)'), [1, $bulan])
            ->groupBy('id_kolok')
            ->pluck('jumlah_rekon', 'id_kolok');

        // Ambil jumlah data belum direkon dari sqlsrv_3 (rekon_bku_belum) berdasarkan tahun dan bulan <= bulan yang dipilih
        $rekonBkuBelum = DB::connection('sqlsrv_3')
            ->table('rekon_bku_belum')
            ->select('id_kolok', DB::raw('COUNT(*) as jumlah_belum_rekon'))
            ->whereYear('tgl_post', $tahun)
            ->whereBetween(DB::raw('MONTH(tgl_post)'), [1, $bulan])
            ->groupBy('id_kolok')
            ->pluck('jumlah_belum_rekon', 'id_kolok');

        // Gabungkan semua data
        $mergedData = $bpadmasterData->map(function ($master) use ($bpadinventoryData, $rekonBku, $rekonBkuBelum) {
            $inventory = $bpadinventoryData[$master->id_kolok] ?? null;

            $master->smt = $inventory->smt ?? 'No Data Found';
            $master->periode_baso = $inventory->periode_baso ?? 'No Data Found';
            $master->tglba_fisik = $inventory->tglba_fisik ?? 'No Data Found';
            $master->no_bafisik = $inventory->no_bafisik ?? 'No Data Found';

            // Tambahkan jumlah rekon dari rekon_bku
            $master->jumlah_rekon = $rekonBku[$master->id_kolok] ?? 0;

            // Tambahkan jumlah belum rekon dari rekon_bku_belum
            $master->jumlah_belum_rekon = $rekonBkuBelum[$master->id_kolok] ?? 0;

            return $master;
        });


        // Helper untuk query count per kolok/idskpd, hasil di-keyBy agar lookup cepat
        $countBy = function ($connection, $table, $groupCol, $where = []) {
            $query = DB::connection($connection)->table($table)->select($groupCol, DB::raw('COUNT(*) as jumlah'));
            foreach ($where as $w) {
                $query->whereRaw($w[0], $w[1] ?? []);
            }
            return $query->groupBy($groupCol)->get()->keyBy($groupCol);
        };

        // SPPB & BASTSPPB
        $sppbFilters = [
            ['SUBSTRING(LTRIM(RTRIM(noref)), 8, 5) = ?', ['RQ2.1']],
            ['SUBSTRING(LTRIM(RTRIM(noref)), 8, 5) = ?', ['RQ2.3']],
            ['SUBSTRING(LTRIM(RTRIM(noref)), 8, 5) = ?', ['RQ2.4']],
            ['SUBSTRING(LTRIM(RTRIM(noref)), 8, 5) = ?', ['RQ2.5']],
        ];
        $bastFilters = $sppbFilters;

        $sppb = [];
        $bastSPPB = [];
        foreach ($sppbFilters as $i => $filter) {
            $sppb[$i] = $countBy('sqlsrv_2', 'rq_data2025', 'idskpd', [
                $filter,
                ['YEAR(tgl_rq) = ?', [$tahun]],
                ['MONTH(tgl_rq) = ?', [$bulan]],
                ["(stat_rq IS NULL OR stat_rq = '')"],
                ["sts = 1"]
            ]);
            $bastSPPB[$i] = $countBy('sqlsrv_2', 'rq_data2025', 'idskpd', [
                $filter,
                ['YEAR(tgl_rq) = ?', [$tahun]],
                ['MONTH(tgl_rq) = ?', [$bulan]],
                ["stat_rq = 1 AND stat_form IS NULL"],
                ["sts = 1"]
            ]);
        }

        // BASTPHK3, BASTHIBAH
        $bastPHK3 = $countBy('sqlsrv_2', 'bast_data2025', 'kolok', [
            ["tipe_bast = 1"],
            ['YEAR(tgl_bast) = ?', [$tahun]],
            ['MONTH(tgl_bast) = ?', [$bulan]],
            ["(stat_bast IS NULL OR stat_bast = '')"],
            ["sts = 1"]
        ]);
        $bastHIBAH = $countBy('sqlsrv_2', 'bast_data2025', 'kolok', [
            ["tipe_bast = 2"],
            ['YEAR(tgl_bast) = ?', [$tahun]],
            ['MONTH(tgl_bast) = ?', [$bulan]],
            ["(stat_bast IS NULL OR stat_bast = '')"],
            ["sts = 1"]
        ]);

        // BASTTRANSFER
        $bastTRANSFER = $countBy('sqlsrv_2', 'rq_data2025', 'idkolok', [
            ["SUBSTRING(noref, 10, 3) = '2.6'"],
            ['YEAR(tgl_rq) = ?', [$tahun]],
            ['MONTH(tgl_rq) = ?', [$bulan]],
            ["stat_rq = 1 AND stat_form IS NULL"],
            ["sts = 1"]
        ]);

        // Tambah/Kurang
        $reviewTambah = $countBy('sqlsrv_2', 'bast_data2025', 'kolok', [
            ["tipe_bast = 7"],
            ['YEAR(tgl_bast) = ?', [$tahun]],
            ['MONTH(tgl_bast) = ?', [$bulan]],
            ["(stat_bast IS NULL OR stat_bast = '')"],
            ["sts = 1"]
        ]);
        $instTambah = $countBy('sqlsrv_2', 'bast_data2025', 'kolok', [
            ["tipe_bast = 5"],
            ['YEAR(tgl_bast) = ?', [$tahun]],
            ['MONTH(tgl_bast) = ?', [$bulan]],
            ["(stat_bast IS NULL OR stat_bast = '')"],
            ["sts = 1"]
        ]);
        $auditTambah = $countBy('sqlsrv_2', 'bast_data2025', 'kolok', [
            ["tipe_bast = 6"],
            ['YEAR(tgl_bast) = ?', [$tahun]],
            ['MONTH(tgl_bast) = ?', [$bulan]],
            ["(stat_bast IS NULL OR stat_bast = '')"],
            ["sts = 1"]
        ]);
        $reviewKurang = $countBy('sqlsrv_2', 'rq_data2025', 'idkolok', [
            ["SUBSTRING(noref, 10, 3) = '4.6'"],
            ['YEAR(tgl_rq) = ?', [$tahun]],
            ['MONTH(tgl_rq) = ?', [$bulan]],
            ["stat_rq = 1 AND stat_form IS NULL"],
            ["sts = 1"]
        ]);
        $insKurang = $countBy('sqlsrv_2', 'rq_data2025', 'idkolok', [
            ["SUBSTRING(noref, 10, 3) = '4.2'"],
            ['YEAR(tgl_rq) = ?', [$tahun]],
            ['MONTH(tgl_rq) = ?', [$bulan]],
            ["stat_rq = 1 AND stat_form IS NULL"],
            ["sts = 1"]
        ]);
        $auditKurang = $countBy('sqlsrv_2', 'rq_data2025', 'idkolok', [
            ["SUBSTRING(noref, 10, 3) = '4.4'"],
            ['YEAR(tgl_rq) = ?', [$tahun]],
            ['MONTH(tgl_rq) = ?', [$bulan]],
            ["stat_rq = 1 AND stat_form IS NULL"],
            ["sts = 1"]
        ]);

        // Semester
        $smt1Tambah = $countBy('sqlsrv_2', 'bast_data2025', 'kolok', [
            ["tipe_bast = '+'"],
            ["(stat_bast IS NULL OR stat_bast = '')"],
            ["sts = 1"],
            ["MONTH(tgl_bast) < 7"]
        ]);
        $smt2Tambah = $countBy('sqlsrv_2', 'bast_data2025', 'kolok', [
            ["tipe_bast = '+'"],
            ["(stat_bast IS NULL OR stat_bast = '')"],
            ["sts = 1"],
            ["MONTH(tgl_bast) > 6"]
        ]);
        $smt1Kurang = $countBy('sqlsrv_2', 'rq_data2025', 'idkolok', [
            ["SUBSTRING(noref, 8, 4) = 'KR2.'"],
            ['YEAR(tgl_rq) = ?', [$tahun]],
            ["stat_rq = 1 AND stat_form IS NULL"],
            ["sts = 1"],
            ["MONTH(tgl_rq) < 7"]
        ]);
        $smt2Kurang = $countBy('sqlsrv_2', 'rq_data2025', 'idkolok', [
            ["SUBSTRING(noref, 8, 4) = 'KR2.'"],
            ['YEAR(tgl_rq) = ?', [$tahun]],
            ["stat_rq = 1 AND stat_form IS NULL"],
            ["sts = 1"],
            ["MONTH(tgl_rq) > 6"]
        ]);

        // Gabungkan semua data ke mergedData
        $mergedData = $mergedData->map(function ($item) use (
            $sppb, $bastSPPB, $bastPHK3, $bastHIBAH, $bastTRANSFER,
            $reviewTambah, $instTambah, $auditTambah, $smt1Tambah, $smt2Tambah,
            $reviewKurang, $insKurang, $auditKurang, $smt1Kurang, $smt2Kurang,
        ) {
            $id = $item->id_kolok;
            $item->SPPB1 = $sppb[0][$id]->jumlah ?? 0;
            $item->SPPB2 = $sppb[1][$id]->jumlah ?? 0;
            $item->SPPB3 = $sppb[2][$id]->jumlah ?? 0;
            $item->SPPB4 = $sppb[3][$id]->jumlah ?? 0;
            $item->BASTSPPB1 = $bastSPPB[0][$id]->jumlah ?? 0;
            $item->BASTSPPB2 = $bastSPPB[1][$id]->jumlah ?? 0;
            $item->BASTSPPB3 = $bastSPPB[2][$id]->jumlah ?? 0;
            $item->BASTSPPB4 = $bastSPPB[3][$id]->jumlah ?? 0;
            $item->BASTPHK3 = $bastPHK3[$id]->jumlah ?? 0;
            $item->BASTHIBAH = $bastHIBAH[$id]->jumlah ?? 0;
            $item->BASTTRANSFER = $bastTRANSFER[$id]->jumlah ?? 0;
            $item->REVIEWTAMBAH = $reviewTambah[$id]->jumlah ?? 0;
            $item->INSTAMBAH = $instTambah[$id]->jumlah ?? 0;
            $item->AUDITTAMBAH = $auditTambah[$id]->jumlah ?? 0;
            $item->SMT1TAMBAH = $smt1Tambah[$id]->jumlah ?? 0;
            $item->SMT2TAMBAH = $smt2Tambah[$id]->jumlah ?? 0;
            $item->REVIEWKURANG = $reviewKurang[$id]->jumlah ?? 0;
            $item->INSKURANG = $insKurang[$id]->jumlah ?? 0;
            $item->AUDITKURANG = $auditKurang[$id]->jumlah ?? 0;
            $item->SMT1KURANG = $smt1Kurang[$id]->jumlah ?? 0;
            $item->SMT2KURANG = $smt2Kurang[$id]->jumlah ?? 0;
            $item->Total_SPPB_BAST =
                $item->SPPB1 + $item->SPPB2 + $item->SPPB3 + $item->SPPB4 +
                $item->BASTSPPB1 + $item->BASTSPPB2 + $item->BASTSPPB3 + $item->BASTSPPB4 +
                $item->BASTPHK3 + $item->BASTHIBAH + $item->BASTTRANSFER +
                $item->REVIEWTAMBAH + $item->INSTAMBAH + $item->AUDITTAMBAH +
                $item->SMT1TAMBAH + $item->SMT2TAMBAH +
                $item->REVIEWKURANG + $item->INSKURANG + $item->AUDITKURANG +
                $item->SMT1KURANG + $item->SMT2KURANG;
            return $item;
        });

        // Hitung selesai/belum
        $sekolahSudah = $sekolahBelum = 0;
        foreach ($mergedData as $item) {
            $jumlahRekon = $item->jumlah_rekon ?? 0;
            $jumlahBelumRekon = $item->jumlah_belum_rekon ?? 0;

        
            if ($item->upb_sekolah == 'Y') {
                if (
                    ($item->Total_SPPB_BAST == 0) &&
                    ($item->tglba_fisik !== 'No Data Found' && !is_null($item->tglba_fisik)) &&
                    ($item->periode_baso !== 'No Data Found' && !is_null($item->periode_baso)) &&
                    ($jumlahBelumRekon == 0 && $jumlahRekon >= 0)
                ) {
                    $sekolahSudah++;
                } else {
                    $sekolahBelum++;
                }
            }
        }

        return view('Backend.persediaansekolah', [
            'mergedData' => $mergedData,
            'bulan' => $bulan,
            'sekolahSudah' => $sekolahSudah,
            'sekolahBelum' => $sekolahBelum,
            'user' => $user,
        ]);
    }

    public function showBlud(Request $request)
    {
        $user = session('user');
        if (!$user) {
            return redirect()->route('login')->withErrors(['login_error' => 'Please log in first.']);
        }

        // Set tahun dan bulan, gunakan variabel agar mudah diubah
        $tahun = 2025;
        // Ambil bulan dari request, default ke 4 (April) jika tidak ada
        $bulan = request('bulan', 4);
        // Validasi agar hanya 1-12 yang diterima
        if (!in_array((int)$bulan, range(1, 12))) {
            $bulan = 4;
        }

        // Ambil master data dan inventory data sekaligus, hanya kolom yang diperlukan
        $bpadmasterData = DB::connection('sqlsrv')->table('master_profile')
            ->join('master_profile_detail', 'master_profile.id_kolok', '=', 'master_profile_detail.id_kolok')
            ->where('master_profile_detail.tahun', $tahun)
            ->where('master_profile_detail.flag_blud', 'Y')
            ->where('master_profile_detail.sts', '1')
            ->select(
                'master_profile.id_kolok',
                'master_profile.nalok',
                'master_profile_detail.tahun',
                'master_profile_detail.upb_sekolah',
                'master_profile_detail.flag_blud'
            )
            ->get()
            ->keyBy('id_kolok');

        $bpadinventoryData = DB::connection('sqlsrv_2')->table('so_data2025')
            ->whereYear('periode_baso', $tahun)
            ->whereMonth('periode_baso', $bulan)
            ->select('kolok', 'smt', 'periode_baso', 'tglba_fisik', 'no_bafisik')
            ->get()
            ->keyBy('kolok');


        // Ambil jumlah data rekon dari sqlsrv_3 (rekon_bku) berdasarkan tahun dan bulan <= bulan yang dipilih
        $rekonBku = DB::connection('sqlsrv_3')
            ->table('rekon_bku')
            ->select('id_kolok', DB::raw('COUNT(*) as jumlah_rekon'))
            ->whereYear('tgl_post', $tahun)
            ->whereBetween(DB::raw('MONTH(tgl_post)'), [1, $bulan])
            ->groupBy('id_kolok')
            ->pluck('jumlah_rekon', 'id_kolok');

        // Ambil jumlah data belum direkon dari sqlsrv_3 (rekon_bku_belum) berdasarkan tahun dan bulan <= bulan yang dipilih
        $rekonBkuBelum = DB::connection('sqlsrv_3')
            ->table('rekon_bku_belum')
            ->select('id_kolok', DB::raw('COUNT(*) as jumlah_belum_rekon'))
            ->whereYear('tgl_post', $tahun)
            ->whereBetween(DB::raw('MONTH(tgl_post)'), [1, $bulan])
            ->groupBy('id_kolok')
            ->pluck('jumlah_belum_rekon', 'id_kolok');

        // Gabungkan semua data
        $mergedData = $bpadmasterData->map(function ($master) use ($bpadinventoryData, $rekonBku, $rekonBkuBelum) {
            $inventory = $bpadinventoryData[$master->id_kolok] ?? null;

            $master->smt = $inventory->smt ?? 'No Data Found';
            $master->periode_baso = $inventory->periode_baso ?? 'No Data Found';
            $master->tglba_fisik = $inventory->tglba_fisik ?? 'No Data Found';
            $master->no_bafisik = $inventory->no_bafisik ?? 'No Data Found';

            // Tambahkan jumlah rekon dari rekon_bku
            $master->jumlah_rekon = $rekonBku[$master->id_kolok] ?? 0;

            // Tambahkan jumlah belum rekon dari rekon_bku_belum
            $master->jumlah_belum_rekon = $rekonBkuBelum[$master->id_kolok] ?? 0;

            return $master;
        });


        // Helper untuk query count per kolok/idskpd, hasil di-keyBy agar lookup cepat
        $countBy = function ($connection, $table, $groupCol, $where = []) {
            $query = DB::connection($connection)->table($table)->select($groupCol, DB::raw('COUNT(*) as jumlah'));
            foreach ($where as $w) {
                $query->whereRaw($w[0], $w[1] ?? []);
            }
            return $query->groupBy($groupCol)->get()->keyBy($groupCol);
        };

        // SPPB & BASTSPPB
        $sppbFilters = [
            ['SUBSTRING(LTRIM(RTRIM(noref)), 8, 5) = ?', ['RQ2.1']],
            ['SUBSTRING(LTRIM(RTRIM(noref)), 8, 5) = ?', ['RQ2.3']],
            ['SUBSTRING(LTRIM(RTRIM(noref)), 8, 5) = ?', ['RQ2.4']],
            ['SUBSTRING(LTRIM(RTRIM(noref)), 8, 5) = ?', ['RQ2.5']],
        ];
        $bastFilters = $sppbFilters;

        $sppb = [];
        $bastSPPB = [];
        foreach ($sppbFilters as $i => $filter) {
            $sppb[$i] = $countBy('sqlsrv_2', 'rq_data2025', 'idskpd', [
                $filter,
                ['YEAR(tgl_rq) = ?', [$tahun]],
                ['MONTH(tgl_rq) = ?', [$bulan]],
                ["(stat_rq IS NULL OR stat_rq = '')"],
                ["sts = 1"]
            ]);
            $bastSPPB[$i] = $countBy('sqlsrv_2', 'rq_data2025', 'idskpd', [
                $filter,
                ['YEAR(tgl_rq) = ?', [$tahun]],
                ['MONTH(tgl_rq) = ?', [$bulan]],
                ["stat_rq = 1 AND stat_form IS NULL"],
                ["sts = 1"]
            ]);
        }

        // BASTPHK3, BASTHIBAH
        $bastPHK3 = $countBy('sqlsrv_2', 'bast_data2025', 'kolok', [
            ["tipe_bast = 1"],
            ['YEAR(tgl_bast) = ?', [$tahun]],
            ['MONTH(tgl_bast) = ?', [$bulan]],
            ["(stat_bast IS NULL OR stat_bast = '')"],
            ["sts = 1"]
        ]);
        $bastHIBAH = $countBy('sqlsrv_2', 'bast_data2025', 'kolok', [
            ["tipe_bast = 2"],
            ['YEAR(tgl_bast) = ?', [$tahun]],
            ['MONTH(tgl_bast) = ?', [$bulan]],
            ["(stat_bast IS NULL OR stat_bast = '')"],
            ["sts = 1"]
        ]);

        // BASTTRANSFER
        $bastTRANSFER = $countBy('sqlsrv_2', 'rq_data2025', 'idkolok', [
            ["SUBSTRING(noref, 10, 3) = '2.6'"],
            ['YEAR(tgl_rq) = ?', [$tahun]],
            ['MONTH(tgl_rq) = ?', [$bulan]],
            ["stat_rq = 1 AND stat_form IS NULL"],
            ["sts = 1"]
        ]);

        // Tambah/Kurang
        $reviewTambah = $countBy('sqlsrv_2', 'bast_data2025', 'kolok', [
            ["tipe_bast = 7"],
            ['YEAR(tgl_bast) = ?', [$tahun]],
            ['MONTH(tgl_bast) = ?', [$bulan]],
            ["(stat_bast IS NULL OR stat_bast = '')"],
            ["sts = 1"]
        ]);
        $instTambah = $countBy('sqlsrv_2', 'bast_data2025', 'kolok', [
            ["tipe_bast = 5"],
            ['YEAR(tgl_bast) = ?', [$tahun]],
            ['MONTH(tgl_bast) = ?', [$bulan]],
            ["(stat_bast IS NULL OR stat_bast = '')"],
            ["sts = 1"]
        ]);
        $auditTambah = $countBy('sqlsrv_2', 'bast_data2025', 'kolok', [
            ["tipe_bast = 6"],
            ['YEAR(tgl_bast) = ?', [$tahun]],
            ['MONTH(tgl_bast) = ?', [$bulan]],
            ["(stat_bast IS NULL OR stat_bast = '')"],
            ["sts = 1"]
        ]);
        $reviewKurang = $countBy('sqlsrv_2', 'rq_data2025', 'idkolok', [
            ["SUBSTRING(noref, 10, 3) = '4.6'"],
            ['YEAR(tgl_rq) = ?', [$tahun]],
            ['MONTH(tgl_rq) = ?', [$bulan]],
            ["stat_rq = 1 AND stat_form IS NULL"],
            ["sts = 1"]
        ]);
        $insKurang = $countBy('sqlsrv_2', 'rq_data2025', 'idkolok', [
            ["SUBSTRING(noref, 10, 3) = '4.2'"],
            ['YEAR(tgl_rq) = ?', [$tahun]],
            ['MONTH(tgl_rq) = ?', [$bulan]],
            ["stat_rq = 1 AND stat_form IS NULL"],
            ["sts = 1"]
        ]);
        $auditKurang = $countBy('sqlsrv_2', 'rq_data2025', 'idkolok', [
            ["SUBSTRING(noref, 10, 3) = '4.4'"],
            ['YEAR(tgl_rq) = ?', [$tahun]],
            ['MONTH(tgl_rq) = ?', [$bulan]],
            ["stat_rq = 1 AND stat_form IS NULL"],
            ["sts = 1"]
        ]);

        // Semester
        $smt1Tambah = $countBy('sqlsrv_2', 'bast_data2025', 'kolok', [
            ["tipe_bast = '+'"],
            ["(stat_bast IS NULL OR stat_bast = '')"],
            ["sts = 1"],
            ["MONTH(tgl_bast) < 7"]
        ]);
        $smt2Tambah = $countBy('sqlsrv_2', 'bast_data2025', 'kolok', [
            ["tipe_bast = '+'"],
            ["(stat_bast IS NULL OR stat_bast = '')"],
            ["sts = 1"],
            ["MONTH(tgl_bast) > 6"]
        ]);
        $smt1Kurang = $countBy('sqlsrv_2', 'rq_data2025', 'idkolok', [
            ["SUBSTRING(noref, 8, 4) = 'KR2.'"],
            ['YEAR(tgl_rq) = ?', [$tahun]],
            ["stat_rq = 1 AND stat_form IS NULL"],
            ["sts = 1"],
            ["MONTH(tgl_rq) < 7"]
        ]);
        $smt2Kurang = $countBy('sqlsrv_2', 'rq_data2025', 'idkolok', [
            ["SUBSTRING(noref, 8, 4) = 'KR2.'"],
            ['YEAR(tgl_rq) = ?', [$tahun]],
            ["stat_rq = 1 AND stat_form IS NULL"],
            ["sts = 1"],
            ["MONTH(tgl_rq) > 6"]
        ]);

        // Gabungkan semua data ke mergedData
        $mergedData = $mergedData->map(function ($item) use (
            $sppb, $bastSPPB, $bastPHK3, $bastHIBAH, $bastTRANSFER,
            $reviewTambah, $instTambah, $auditTambah, $smt1Tambah, $smt2Tambah,
            $reviewKurang, $insKurang, $auditKurang, $smt1Kurang, $smt2Kurang,
        ) {
            $id = $item->id_kolok;
            $item->SPPB1 = $sppb[0][$id]->jumlah ?? 0;
            $item->SPPB2 = $sppb[1][$id]->jumlah ?? 0;
            $item->SPPB3 = $sppb[2][$id]->jumlah ?? 0;
            $item->SPPB4 = $sppb[3][$id]->jumlah ?? 0;
            $item->BASTSPPB1 = $bastSPPB[0][$id]->jumlah ?? 0;
            $item->BASTSPPB2 = $bastSPPB[1][$id]->jumlah ?? 0;
            $item->BASTSPPB3 = $bastSPPB[2][$id]->jumlah ?? 0;
            $item->BASTSPPB4 = $bastSPPB[3][$id]->jumlah ?? 0;
            $item->BASTPHK3 = $bastPHK3[$id]->jumlah ?? 0;
            $item->BASTHIBAH = $bastHIBAH[$id]->jumlah ?? 0;
            $item->BASTTRANSFER = $bastTRANSFER[$id]->jumlah ?? 0;
            $item->REVIEWTAMBAH = $reviewTambah[$id]->jumlah ?? 0;
            $item->INSTAMBAH = $instTambah[$id]->jumlah ?? 0;
            $item->AUDITTAMBAH = $auditTambah[$id]->jumlah ?? 0;
            $item->SMT1TAMBAH = $smt1Tambah[$id]->jumlah ?? 0;
            $item->SMT2TAMBAH = $smt2Tambah[$id]->jumlah ?? 0;
            $item->REVIEWKURANG = $reviewKurang[$id]->jumlah ?? 0;
            $item->INSKURANG = $insKurang[$id]->jumlah ?? 0;
            $item->AUDITKURANG = $auditKurang[$id]->jumlah ?? 0;
            $item->SMT1KURANG = $smt1Kurang[$id]->jumlah ?? 0;
            $item->SMT2KURANG = $smt2Kurang[$id]->jumlah ?? 0;
            $item->Total_SPPB_BAST =
                $item->SPPB1 + $item->SPPB2 + $item->SPPB3 + $item->SPPB4 +
                $item->BASTSPPB1 + $item->BASTSPPB2 + $item->BASTSPPB3 + $item->BASTSPPB4 +
                $item->BASTPHK3 + $item->BASTHIBAH + $item->BASTTRANSFER +
                $item->REVIEWTAMBAH + $item->INSTAMBAH + $item->AUDITTAMBAH +
                $item->SMT1TAMBAH + $item->SMT2TAMBAH +
                $item->REVIEWKURANG + $item->INSKURANG + $item->AUDITKURANG +
                $item->SMT1KURANG + $item->SMT2KURANG;
            return $item;
        });

        // Hitung selesai/belum
        $bludSudah = $bludBelum = 0;
        foreach ($mergedData as $item) {
            $jumlahRekon = $item->jumlah_rekon ?? 0;
            $jumlahBelumRekon = $item->jumlah_belum_rekon ?? 0;

            if ($item->flag_blud == 'Y') {
                if (
                    ($item->Total_SPPB_BAST == 0) &&
                    ($item->tglba_fisik !== 'No Data Found' && !is_null($item->tglba_fisik)) &&
                    ($item->periode_baso !== 'No Data Found' && !is_null($item->periode_baso)) &&
                    ($jumlahBelumRekon == 0 && $jumlahRekon >= 0)
                ) {
                    $bludSudah++;
                } else {
                    $bludBelum++;
                }
            }
        }

        return view('Backend.persediaanblud', [
            'mergedData' => $mergedData,
            'bulan' => $bulan,
            'bludSudah' => $bludSudah,
            'bludBelum' => $bludBelum,
            'user' => $user,
        ]);
    }

    public function semester1(Request $request)
    {
        $user = session('user');
        if (!$user) {
            return redirect()->route('login')->withErrors(['login_error' => 'Please log in first.']);
        }

        // Set tahun dan bulan, gunakan variabel agar mudah diubah
        $tahun = 2025;

        // Ambil master data dan inventory data sekaligus, hanya kolom yang diperlukan
        $bpadmasterData = DB::connection('sqlsrv')->table('master_profile')
            ->join('master_profile_detail', 'master_profile.id_kolok', '=', 'master_profile_detail.id_kolok')
            ->where('master_profile_detail.tahun', $tahun)
            ->where('master_profile_detail.sts', '1')
            ->select(
                'master_profile.id_kolok',
                'master_profile.nalok',
                'master_profile_detail.tahun',
                'master_profile_detail.upb_sekolah',
                'master_profile_detail.flag_blud'
            )
            ->get()
            ->keyBy('id_kolok');

        $bpadinventoryData = DB::connection('sqlsrv_2')->table('so_data2025')
            ->whereYear('periode_baso', $tahun)
            ->whereMonth('periode_baso', range(1, 6))
            ->select('kolok', 'smt', 'periode_baso', 'tglba_fisik', 'no_bafisik')
            ->get()
            ->keyBy('kolok');


        // Ambil jumlah data rekon dari sqlsrv_3 (rekon_bku) berdasarkan tahun dan bulan <= bulan yang dipilih
        $rekonBku = DB::connection('sqlsrv_3')
            ->table('rekon_bku')
            ->select('id_kolok', DB::raw('COUNT(*) as jumlah_rekon'))
            ->whereYear('tgl_post', $tahun)
            ->whereBetween(DB::raw('MONTH(tgl_post)'), [1, range(1, 6)])
            ->groupBy('id_kolok')
            ->pluck('jumlah_rekon', 'id_kolok');

        // Ambil jumlah data belum direkon dari sqlsrv_3 (rekon_bku_belum) berdasarkan tahun dan bulan <= bulan yang dipilih
        $rekonBkuBelum = DB::connection('sqlsrv_3')
            ->table('rekon_bku_belum')
            ->select('id_kolok', DB::raw('COUNT(*) as jumlah_belum_rekon'))
            ->whereYear('tgl_post', $tahun)
            ->whereBetween(DB::raw('MONTH(tgl_post)'), [1, range(1, 6)])
            ->groupBy('id_kolok')
            ->pluck('jumlah_belum_rekon', 'id_kolok');

        // Gabungkan semua data
        $mergedData = $bpadmasterData->map(function ($master) use ($bpadinventoryData, $rekonBku, $rekonBkuBelum) {
            $inventory = $bpadinventoryData[$master->id_kolok] ?? null;

            $master->smt = $inventory->smt ?? 'No Data Found';
            $master->periode_baso = $inventory->periode_baso ?? 'No Data Found';
            $master->tglba_fisik = $inventory->tglba_fisik ?? 'No Data Found';
            $master->no_bafisik = $inventory->no_bafisik ?? 'No Data Found';

            // Tambahkan jumlah rekon dari rekon_bku
            $master->jumlah_rekon = $rekonBku[$master->id_kolok] ?? 0;

            // Tambahkan jumlah belum rekon dari rekon_bku_belum
            $master->jumlah_belum_rekon = $rekonBkuBelum[$master->id_kolok] ?? 0;

            return $master;
        });


        // Helper untuk query count per kolok/idskpd, hasil di-keyBy agar lookup cepat
        $countBy = function ($connection, $table, $groupCol, $where = []) {
            $query = DB::connection($connection)->table($table)->select($groupCol, DB::raw('COUNT(*) as jumlah'));
            foreach ($where as $w) {
                $query->whereRaw($w[0], $w[1] ?? []);
            }
            return $query->groupBy($groupCol)->get()->keyBy($groupCol);
        };

        // SPPB & BASTSPPB
        $sppbFilters = [
            ['SUBSTRING(LTRIM(RTRIM(noref)), 8, 5) = ?', ['RQ2.1']],
            ['SUBSTRING(LTRIM(RTRIM(noref)), 8, 5) = ?', ['RQ2.3']],
            ['SUBSTRING(LTRIM(RTRIM(noref)), 8, 5) = ?', ['RQ2.4']],
            ['SUBSTRING(LTRIM(RTRIM(noref)), 8, 5) = ?', ['RQ2.5']],
        ];
        $bastFilters = $sppbFilters;

        $sppb = [];
        $bastSPPB = [];
        foreach ($sppbFilters as $i => $filter) {
            $sppb[$i] = $countBy('sqlsrv_2', 'rq_data2025', 'idskpd', [
                $filter,
                ['YEAR(tgl_rq) = ?', [$tahun]],
                ['MONTH(tgl_rq) = ?', [range(1, 6)]],
                ["(stat_rq IS NULL OR stat_rq = '')"],
                ["sts = 1"]
            ]);
            $bastSPPB[$i] = $countBy('sqlsrv_2', 'rq_data2025', 'idskpd', [
                $filter,
                ['YEAR(tgl_rq) = ?', [$tahun]],
                ['MONTH(tgl_rq) = ?', [range(1, 6)]],
                ["stat_rq = 1 AND stat_form IS NULL"],
                ["sts = 1"]
            ]);
        }

        // BASTPHK3, BASTHIBAH
        $bastPHK3 = $countBy('sqlsrv_2', 'bast_data2025', 'kolok', [
            ["tipe_bast = 1"],
            ['YEAR(tgl_bast) = ?', [$tahun]],
            ['MONTH(tgl_bast) = ?', [range(1, 6)]],
            ["(stat_bast IS NULL OR stat_bast = '')"],
            ["sts = 1"]
        ]);
        $bastHIBAH = $countBy('sqlsrv_2', 'bast_data2025', 'kolok', [
            ["tipe_bast = 2"],
            ['YEAR(tgl_bast) = ?', [$tahun]],
            ['MONTH(tgl_bast) = ?', [range(1, 6)]],
            ["(stat_bast IS NULL OR stat_bast = '')"],
            ["sts = 1"]
        ]);

        // BASTTRANSFER
        $bastTRANSFER = $countBy('sqlsrv_2', 'rq_data2025', 'idkolok', [
            ["SUBSTRING(noref, 10, 3) = '2.6'"],
            ['YEAR(tgl_rq) = ?', [$tahun]],
            ['MONTH(tgl_rq) = ?', [range(1, 6)]],
            ["stat_rq = 1 AND stat_form IS NULL"],
            ["sts = 1"]
        ]);

        // Tambah/Kurang
        $reviewTambah = $countBy('sqlsrv_2', 'bast_data2025', 'kolok', [
            ["tipe_bast = 7"],
            ['YEAR(tgl_bast) = ?', [$tahun]],
            ['MONTH(tgl_bast) = ?', [range(1, 6)]],
            ["(stat_bast IS NULL OR stat_bast = '')"],
            ["sts = 1"]
        ]);
        $instTambah = $countBy('sqlsrv_2', 'bast_data2025', 'kolok', [
            ["tipe_bast = 5"],
            ['YEAR(tgl_bast) = ?', [$tahun]],
            ['MONTH(tgl_bast) = ?', [range(1, 6)]],
            ["(stat_bast IS NULL OR stat_bast = '')"],
            ["sts = 1"]
        ]);
        $auditTambah = $countBy('sqlsrv_2', 'bast_data2025', 'kolok', [
            ["tipe_bast = 6"],
            ['YEAR(tgl_bast) = ?', [$tahun]],
            ['MONTH(tgl_bast) = ?', [range(1, 6)]],
            ["(stat_bast IS NULL OR stat_bast = '')"],
            ["sts = 1"]
        ]);
        $reviewKurang = $countBy('sqlsrv_2', 'rq_data2025', 'idkolok', [
            ["SUBSTRING(noref, 10, 3) = '4.6'"],
            ['YEAR(tgl_rq) = ?', [$tahun]],
            ['MONTH(tgl_rq) = ?', [range(1, 6)]],
            ["stat_rq = 1 AND stat_form IS NULL"],
            ["sts = 1"]
        ]);
        $insKurang = $countBy('sqlsrv_2', 'rq_data2025', 'idkolok', [
            ["SUBSTRING(noref, 10, 3) = '4.2'"],
            ['YEAR(tgl_rq) = ?', [$tahun]],
            ['MONTH(tgl_rq) = ?', [range(1, 6)]],
            ["stat_rq = 1 AND stat_form IS NULL"],
            ["sts = 1"]
        ]);
        $auditKurang = $countBy('sqlsrv_2', 'rq_data2025', 'idkolok', [
            ["SUBSTRING(noref, 10, 3) = '4.4'"],
            ['YEAR(tgl_rq) = ?', [$tahun]],
            ['MONTH(tgl_rq) = ?', [range(1, 6)]],
            ["stat_rq = 1 AND stat_form IS NULL"],
            ["sts = 1"]
        ]);

        // Semester
        $smt1Tambah = $countBy('sqlsrv_2', 'bast_data2025', 'kolok', [
            ["tipe_bast = '+'"],
            ["(stat_bast IS NULL OR stat_bast = '')"],
            ["sts = 1"],
            ["MONTH(tgl_bast) < 7"]
        ]);
        $smt2Tambah = $countBy('sqlsrv_2', 'bast_data2025', 'kolok', [
            ["tipe_bast = '+'"],
            ["(stat_bast IS NULL OR stat_bast = '')"],
            ["sts = 1"],
            ["MONTH(tgl_bast) > 6"]
        ]);
        $smt1Kurang = $countBy('sqlsrv_2', 'rq_data2025', 'idkolok', [
            ["SUBSTRING(noref, 8, 4) = 'KR2.'"],
            ['YEAR(tgl_rq) = ?', [$tahun]],
            ["stat_rq = 1 AND stat_form IS NULL"],
            ["sts = 1"],
            ["MONTH(tgl_rq) < 7"]
        ]);
        $smt2Kurang = $countBy('sqlsrv_2', 'rq_data2025', 'idkolok', [
            ["SUBSTRING(noref, 8, 4) = 'KR2.'"],
            ['YEAR(tgl_rq) = ?', [$tahun]],
            ["stat_rq = 1 AND stat_form IS NULL"],
            ["sts = 1"],
            ["MONTH(tgl_rq) > 6"]
        ]);

        // Gabungkan semua data ke mergedData
        $mergedData = $mergedData->map(function ($item) use (
            $sppb, $bastSPPB, $bastPHK3, $bastHIBAH, $bastTRANSFER,
            $reviewTambah, $instTambah, $auditTambah, $smt1Tambah, $smt2Tambah,
            $reviewKurang, $insKurang, $auditKurang, $smt1Kurang, $smt2Kurang,
        ) {
            $id = $item->id_kolok;
            $item->SPPB1 = $sppb[0][$id]->jumlah ?? 0;
            $item->SPPB2 = $sppb[1][$id]->jumlah ?? 0;
            $item->SPPB3 = $sppb[2][$id]->jumlah ?? 0;
            $item->SPPB4 = $sppb[3][$id]->jumlah ?? 0;
            $item->BASTSPPB1 = $bastSPPB[0][$id]->jumlah ?? 0;
            $item->BASTSPPB2 = $bastSPPB[1][$id]->jumlah ?? 0;
            $item->BASTSPPB3 = $bastSPPB[2][$id]->jumlah ?? 0;
            $item->BASTSPPB4 = $bastSPPB[3][$id]->jumlah ?? 0;
            $item->BASTPHK3 = $bastPHK3[$id]->jumlah ?? 0;
            $item->BASTHIBAH = $bastHIBAH[$id]->jumlah ?? 0;
            $item->BASTTRANSFER = $bastTRANSFER[$id]->jumlah ?? 0;
            $item->REVIEWTAMBAH = $reviewTambah[$id]->jumlah ?? 0;
            $item->INSTAMBAH = $instTambah[$id]->jumlah ?? 0;
            $item->AUDITTAMBAH = $auditTambah[$id]->jumlah ?? 0;
            $item->SMT1TAMBAH = $smt1Tambah[$id]->jumlah ?? 0;
            $item->SMT2TAMBAH = $smt2Tambah[$id]->jumlah ?? 0;
            $item->REVIEWKURANG = $reviewKurang[$id]->jumlah ?? 0;
            $item->INSKURANG = $insKurang[$id]->jumlah ?? 0;
            $item->AUDITKURANG = $auditKurang[$id]->jumlah ?? 0;
            $item->SMT1KURANG = $smt1Kurang[$id]->jumlah ?? 0;
            $item->SMT2KURANG = $smt2Kurang[$id]->jumlah ?? 0;
            $item->Total_SPPB_BAST =
                $item->SPPB1 + $item->SPPB2 + $item->SPPB3 + $item->SPPB4 +
                $item->BASTSPPB1 + $item->BASTSPPB2 + $item->BASTSPPB3 + $item->BASTSPPB4 +
                $item->BASTPHK3 + $item->BASTHIBAH + $item->BASTTRANSFER +
                $item->REVIEWTAMBAH + $item->INSTAMBAH + $item->AUDITTAMBAH +
                $item->SMT1TAMBAH + $item->SMT2TAMBAH +
                $item->REVIEWKURANG + $item->INSKURANG + $item->AUDITKURANG +
                $item->SMT1KURANG + $item->SMT2KURANG;
            return $item;
        });

        // Hitung selesai/belum
        $selesaiCount = $belumCount = $sekolahSudah = $sekolahBelum = $bludSudah = $bludBelum = 0;
        foreach ($mergedData as $item) {
            $jumlahRekon = $item->jumlah_rekon ?? 0;
            $jumlahBelumRekon = $item->jumlah_belum_rekon ?? 0;

            if ($item->upb_sekolah !== 'Y' && $item->flag_blud !== 'Y') {
                if (
                    ($item->Total_SPPB_BAST == 0) &&
                    ($item->tglba_fisik !== 'No Data Found' && !is_null($item->tglba_fisik)) &&
                    ($item->periode_baso !== 'No Data Found' && !is_null($item->periode_baso)) &&
                    ($jumlahBelumRekon == 0 && $jumlahRekon > 0 || $jumlahRekon == 0)
                ) {
                    $selesaiCount++;
                } else {
                    $belumCount++;
                }
            }
            if ($item->upb_sekolah == 'Y') {
                if (
                    ($item->Total_SPPB_BAST == 0) &&
                    ($item->tglba_fisik !== 'No Data Found' && !is_null($item->tglba_fisik)) &&
                    ($item->periode_baso !== 'No Data Found' && !is_null($item->periode_baso)) &&
                    ($jumlahBelumRekon == 0 && $jumlahRekon > 0 || $jumlahRekon == 0)
                ) {
                    $sekolahSudah++;
                } else {
                    $sekolahBelum++;
                }
            }
            if ($item->flag_blud == 'Y') {
                if (
                    ($item->Total_SPPB_BAST == 0) &&
                    ($item->tglba_fisik !== 'No Data Found' && !is_null($item->tglba_fisik)) &&
                    ($item->periode_baso !== 'No Data Found' && !is_null($item->periode_baso)) &&
                    ($jumlahBelumRekon == 0 && $jumlahRekon >= 0)
                ) {
                    $bludSudah++;
                } else {
                    $bludBelum++;
                }
            }
        }

        return view('Backend.persediaansmt1', [
            'mergedData' => $mergedData,
            'selesaiCount' => $selesaiCount,
            'belumCount' => $belumCount,
            'sekolahSudah' => $sekolahSudah,
            'sekolahBelum' => $sekolahBelum,
            'bludSudah' => $bludSudah,
            'bludBelum' => $bludBelum,
            'user' => $user,
        ]);
    }

    public function getData()
    {
        $username = 'bpad';
        $password = 'bp4d';
        $tahun = '2025';
        $maxData = 10000;
        $dataCount = 0;

        $rekapBelumRekon = [];

        $kodeSkpdList = DB::connection('sqlsrv')->table('master_profile_detail')
            ->where('tahun', $tahun)
            ->where('sts', '1')
            ->whereNull('upb_sekolah')
            ->select('kode_skpd', 'id_kolok', 'nalok', 'id_kolokskpd')
            ->distinct()
            ->get();

        // Ambil kombinasi no_bku + realisasi + no_bukti dari DB
        $dbRekonPairs = DB::connection('sqlsrv_2')
            ->table('rekonbku_detail2025')
            ->select('no_bku', 'realisasi', 'no_bukti', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('no_bku', 'realisasi', 'no_bukti')
            ->get()
            ->reduce(function ($carry, $item) {
                $noBku = trim((string) $item->no_bku);
                $realisasi = number_format((float) $item->realisasi, 2, '.', '');
                $noBukti = trim((string) $item->no_bukti);
                $key = $noBku . '|' . $realisasi . '|' . $noBukti;
                $carry[$key] = $item->jumlah;
                return $carry;
            }, []);

        // Ambil semua kode akun yang flag_ba = 1 dari glo_katbrg
        $akunBA = DB::connection('sqlsrv_2')
            ->table('glo_katbrg')
            ->where('flag_ba', 1)
            ->pluck('kd_akun')
            ->map(function ($val) {
                return trim((string) $val);
            })
            ->toArray();
        $akunBA = array_flip($akunBA); // konversi ke array lookup

        $finalData = [];

        foreach ($kodeSkpdList as $skpd) {
            if ($dataCount >= $maxData) {
                break;
            }

            $response = Http::withBasicAuth($username, $password)
                ->withOptions([
                    'verify' => false,
                    'timeout' => 30,
                    'connect_timeout' => 10,
                ])
                ->get('https://soadki.jakarta.go.id/rest/gov/dki/sipkd/realisasipernobukti2/ws', [
                    'skpd' => $skpd->kode_skpd,
                    'tahun' => $tahun,
                ]);

            if ($response->successful()) {
                $apiData = $response->json();
                $apiResults = $apiData['results'] ?? [];

                foreach ($apiResults as $item) {
                    if ($dataCount >= $maxData) {
                        break 2;
                    }

                    $noBku = trim((string) ($item['I_BKUNO'] ?? ''));
                    $realisasi = number_format((float) ($item['REALISASI'] ?? 0), 2, '.', '');
                    $noBukti = trim((string) ($item['I_DOC_BUKTI'] ?? ''));
                    $key = $noBku . '|' . $realisasi . '|' . $noBukti;

                    $item['id_kolok'] = $skpd->id_kolok;
                    $item['id_kolokskpd'] = $skpd->id_kolokskpd;
                    $item['nalok'] = $skpd->nalok;
                    $item['status_db'] = ($item['KODE_SKPD'] == $skpd->kode_skpd) ? 'Terdaftar' : 'Tidak Ada';

                    // Status rekon berdasarkan 3 kunci
                    $item['rekon_status'] = 'Belum Direkon';
                    if ($noBku && $realisasi && $noBukti && isset($dbRekonPairs[$key]) && $dbRekonPairs[$key] > 0) {
                        $item['rekon_status'] = 'Sudah Direkon';
                        $dbRekonPairs[$key]--;
                    }

                    // Status akun BA
                    $kodeAkun = trim((string) ($item['KODE_AKUN'] ?? ''));
                    $item['akun_ba_status'] = isset($akunBA[$kodeAkun]) ? 'Termasuk BA' : 'Bukan BA';

                    // Rekap per kolok untuk yang sudah direkon
                    if ($item['rekon_status'] === 'Sudah Direkon') {
                        $kolok = $skpd->id_kolok;
                        if (!isset($rekapBelumRekon[$kolok])) {
                            $rekapBelumRekon[$kolok] = 0;
                        }
                        $rekapBelumRekon[$kolok]++;
                    }

                    $finalData[] = $item;
                    $dataCount++;
                }
            }
        }

        return view('Backend.dataapi', [
            'data' => $finalData,
            'rekapBelumRekon' => $rekapBelumRekon
        ]);
    }

}