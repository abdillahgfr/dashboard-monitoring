<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Persediaan;
use App\Models\PersediaanDetail;
use Yajra\DataTables\DataTables;

class HomeController extends Controller
{
    public function index(Request $request)
    {

        $user = session('user');
        
        if (!$user) {
            return redirect()->route('login')->withErrors(['login_error' => 'Please log in first.']);
        }
              
        // Retrieve master data from sqlsrv
        $bpadmasterData = DB::connection('sqlsrv')->table('master_profile')
            ->join('master_profile_detail', 'master_profile.id_kolok', '=', 'master_profile_detail.id_kolok')
            ->where('master_profile_detail.tahun', '2025') // Filter data for the year 2025
            ->where('master_profile_detail.sts', '1')
            ->select('master_profile.id_kolok', 'master_profile.nalok', 'master_profile_detail.tahun', 'master_profile_detail.upb_sekolah', 'master_profile_detail.flag_blud')
            ->get();

        // Query from the second database
        $currentYear = $tahun ?? date('Y'); // Use the selected year or default to the current year
        $previousYear = $currentYear - 1; // Calculate the previous year

        // Add current year and previous year to the view
        $tahunList = collect([$currentYear, $previousYear]);
        
        // Generate a list of months from January to December with rules for formatting
        $listBulan = collect([
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ]);
        $currentMonth = $request->get('smt') ? array_search($request->get('smt'), $listBulan->toArray()) : date('m'); // Retrieve the 'smt' filter from the request or default to the current month
        
        $bpadinventoryData = DB::connection('sqlsrv_2')->table('so_data2025')
            ->whereYear('periode_baso', $currentYear) // Filter by the selected year
            ->whereMonth('periode_baso', '04') // Filter by the selected or current month
            ->select('kolok', 'smt', 'periode_baso', 'tglba_fisik', 'no_bafisik')
            ->get();

        // Merge data from both databases and include 'periode_baso', 'tglba_fisik', and 'TotalSPPB'
        $mergedData = $bpadmasterData->map(function ($master) use ($bpadinventoryData) {
            $inventory = $bpadinventoryData->firstWhere('kolok', $master->id_kolok);
    
                $master->smt = $inventory->smt ?? 'No Data Found';
                $master->periode_baso = $inventory->periode_baso ?? 'No Data Found';
                $master->tglba_fisik = $inventory->tglba_fisik ?? 'No Data Found';
                $master->no_bafisik = $inventory->no_bafisik ?? 'No Data Found';
    
                return $master;
            });

        // Helper function for fetching SPPB data from rq_data2025
        $getSPPBData = function ($connection, $columnAlias, $filter) {
            return DB::connection($connection)
                ->table('rq_data2025')
                ->select('idskpd', DB::raw("COUNT(*) AS $columnAlias"))
                ->whereRaw("SUBSTRING(LTRIM(RTRIM(noref)), 8, 5) = '$filter'")
                ->whereYear('tgl_rq', '2025')
                ->whereMonth('tgl_rq', '4') // Filter for April 2025
                ->where(function ($query) {
                    $query->whereNull('stat_rq')->orWhere('stat_rq', '');
                })
                ->where('sts', '1')
                ->groupBy('idskpd')
                ->get();
        };

        // Retrieve SPPB counts
        $sppb1 = $getSPPBData('sqlsrv_2', 'SPPB1', 'RQ2.1');
        $sppb2 = $getSPPBData('sqlsrv_2', 'SPPB2', 'RQ2.3');
        $sppb3 = $getSPPBData('sqlsrv_2', 'SPPB3', 'RQ2.4');
        $sppb4 = $getSPPBData('sqlsrv_2', 'SPPB4', 'RQ2.5');

        // Helper function for fetching BASTSPPB data from rq_data2025
        $getBASTSPPBData = function ($connection, $columnAlias, $filter) {
            return DB::connection($connection)
                ->table('rq_data2025')
                ->select('idskpd', DB::raw("COUNT(*) AS $columnAlias"))
                ->whereRaw("SUBSTRING(LTRIM(RTRIM(noref)), 8, 5) = '$filter'")
                ->whereYear('tgl_rq', '2025')
                ->whereMonth('tgl_rq', '4') // Filter for April 2025
                ->where(function ($query) {
                    $query->where('stat_rq', '1')->whereNull('stat_form');
                })
                ->where('sts', '1')
                ->groupBy('idskpd')
                ->get();
        };

        // Retrieve BASTSPPB counts
        $bastSPPB1 = $getBASTSPPBData('sqlsrv_2', 'BASTSPPB1', 'RQ2.1');
        $bastSPPB2 = $getBASTSPPBData('sqlsrv_2', 'BASTSPPB2', 'RQ2.3');
        $bastSPPB3 = $getBASTSPPBData('sqlsrv_2', 'BASTSPPB3', 'RQ2.4');
        $bastSPPB4 = $getBASTSPPBData('sqlsrv_2', 'BASTSPPB4', 'RQ2.5');

        // Helper function for fetching BASTPHK3 and BASTHIBAH from bast_data2025
        $getBASTData = function ($connection, $columnAlias, $filter) {
            return DB::connection($connection)
                ->table('bast_data2025')
                ->select('kolok', DB::raw("COUNT(*) AS $columnAlias"))
                ->where('tipe_bast', $filter)
                ->whereYear('tgl_bast', '2025')
                ->whereMonth('tgl_bast', '4') // Filter for April 2025
                ->where(function ($query) {
                    $query->whereNull('stat_bast')->orWhere('stat_bast', '');
                })
                ->where('sts', '1')
                ->groupBy('kolok')
                ->get();
        };

        // Retrieve BASTPHK3 and BASTHIBAH from bast_data2025
        $bastPHK3 = $getBASTData('sqlsrv_2', 'BASTPHK3', '1');
        $bastHIBAH = $getBASTData('sqlsrv_2', 'BASTHIBAH', '2');

        // Retrieve BASTTRANSFER from rq_data2025
        $bastTRANSFER = DB::connection('sqlsrv_2')
            ->table('rq_data2025')
            ->select('idkolok', DB::raw("COUNT(*) AS BASTTRANSFER"))
            ->whereRaw("SUBSTRING(noref, 10, 3) = '2.6'")
            ->whereYear('tgl_rq', '2025')
            ->whereMonth('tgl_rq', '4') // Filter for April 2025
            ->where('stat_rq', '1')
            ->whereNull('stat_form')
            ->where('sts', '1')
            ->groupBy('idkolok')
            ->get();

        // Helper function for fetching "Tambah" data from bast_data2025
        $getTambahData = function ($connection, $columnAlias, $filter) {
            return DB::connection($connection)
                ->table('bast_data2025')
                ->select('kolok', DB::raw("COUNT(*) AS $columnAlias"))
                ->where('tipe_bast', $filter)
                ->whereYear('tgl_bast', '2025')
                ->whereMonth('tgl_bast', '4') // Filter for April 2025
                ->where(function ($query) {
                    $query->whereNull('stat_bast')->orWhere('stat_bast', '');
                })
                ->where('sts', '1')
                ->groupBy('kolok')
                ->get();
        };

        // Helper function for fetching "Kurang" data from rq_data2025
        $getKurangData = function ($connection, $columnAlias, $filter) {
            return DB::connection($connection)
                ->table('rq_data2025')
                ->select('idkolok', DB::raw("COUNT(*) AS $columnAlias"))
                ->whereRaw("SUBSTRING(noref, 10, 3) = '$filter'")
                ->whereYear('tgl_rq', '2025')
                ->whereMonth('tgl_rq', '4') // Filter for April 2025
                ->where('stat_rq', '1')
                ->whereNull('stat_form')
                ->where('sts', '1')
                ->groupBy('idkolok')
                ->get();
        };

        // Retrieve "Tambah" data
        $reviewTambah = $getTambahData('sqlsrv_2', 'REVIEWTAMBAH', '7');
        $instTambah = $getTambahData('sqlsrv_2', 'INSTAMBAH', '5');
        $auditTambah = $getTambahData('sqlsrv_2', 'AUDITTAMBAH', '6');

        // Retrieve "Kurang" data
        $reviewKurang = $getKurangData('sqlsrv_2', 'REVIEWKURANG', '4.6');
        $insKurang = $getKurangData('sqlsrv_2', 'INSKURANG', '4.2');
        $auditKurang = $getKurangData('sqlsrv_2', 'AUDITKURANG', '4.4');

        // Retrieve semester-based data
        $smt1Tambah = DB::connection('sqlsrv_2')->table('bast_data2025')
            ->select('kolok', DB::raw("COUNT(*) AS SMT1TAMBAH"))
            ->where('tipe_bast', '+')
            ->where(function ($query) {
                $query->whereNull('stat_bast')->orWhere('stat_bast', '');
            })
            ->where('sts', '1')
            ->whereMonth('tgl_bast', '<', 7)
            ->groupBy('kolok')
            ->get();

        $smt2Tambah = DB::connection('sqlsrv_2')->table('bast_data2025')
            ->select('kolok', DB::raw("COUNT(*) AS SMT2TAMBAH"))
            ->where('tipe_bast', '+')
            ->where(function ($query) {
                $query->whereNull('stat_bast')->orWhere('stat_bast', '');
            })
            ->where('sts', '1')
            ->whereMonth('tgl_bast', '>', 6)
            ->groupBy('kolok')
            ->get();

        $smt1Kurang = DB::connection('sqlsrv_2')->table('rq_data2025')
            ->select('idkolok', DB::raw("COUNT(*) AS SMT1KURANG"))
            ->whereRaw("SUBSTRING(noref, 8, 4) = 'KR2.'")
            ->whereYear('tgl_rq', '2025')
            ->where('stat_rq', '1')
            ->whereNull('stat_form')
            ->where('sts', '1')
            ->whereMonth('tgl_rq', '<', 7)
            ->groupBy('idkolok')
            ->get();

        $smt2Kurang = DB::connection('sqlsrv_2')->table('rq_data2025')
            ->select('idkolok', DB::raw("COUNT(*) AS SMT2KURANG"))
            ->whereRaw("SUBSTRING(noref, 8, 4) = 'KR2.'")
            ->whereYear('tgl_rq', '2025')
            ->where('stat_rq', '1')
            ->whereNull('stat_form')
            ->where('sts', '1')
            ->whereMonth('tgl_rq', '>', 6)
            ->groupBy('idkolok')
            ->get();


        // Merge data into master
        $mergedData = $mergedData->map(function ($item) use ($sppb1, $sppb2, $sppb3, $sppb4, $bastSPPB1, $bastSPPB2, $bastSPPB3, $bastSPPB4, $bastPHK3, $bastHIBAH, $bastTRANSFER, $reviewTambah, $instTambah, $auditTambah, $smt1Tambah, $smt2Tambah, $reviewKurang, $insKurang, $auditKurang, $smt1Kurang, $smt2Kurang) {
            $item->SPPB1 = optional($sppb1->firstWhere('idskpd', $item->id_kolok))->SPPB1 ?? 0;
            $item->SPPB2 = optional($sppb2->firstWhere('idskpd', $item->id_kolok))->SPPB2 ?? 0;
            $item->SPPB3 = optional($sppb3->firstWhere('idskpd', $item->id_kolok))->SPPB3 ?? 0;
            $item->SPPB4 = optional($sppb4->firstWhere('idskpd', $item->id_kolok))->SPPB4 ?? 0;
        
            $item->BASTSPPB1 = optional($bastSPPB1->firstWhere('idskpd', $item->id_kolok))->BASTSPPB1 ?? 0;
            $item->BASTSPPB2 = optional($bastSPPB2->firstWhere('idskpd', $item->id_kolok))->BASTSPPB2 ?? 0;
            $item->BASTSPPB3 = optional($bastSPPB3->firstWhere('idskpd', $item->id_kolok))->BASTSPPB3 ?? 0;
            $item->BASTSPPB4 = optional($bastSPPB4->firstWhere('idskpd', $item->id_kolok))->BASTSPPB4 ?? 0;
        
            $item->BASTPHK3 = optional($bastPHK3->firstWhere('kolok', $item->id_kolok))->BASTPHK3 ?? 0;
            $item->BASTHIBAH = optional($bastHIBAH->firstWhere('kolok', $item->id_kolok))->BASTHIBAH ?? 0;
            $item->BASTTRANSFER = optional($bastTRANSFER->firstWhere('idkolok', $item->id_kolok))->BASTTRANSFER ?? 0;
        
            $item->REVIEWTAMBAH = optional($reviewTambah->firstWhere('kolok', $item->id_kolok))->REVIEWTAMBAH ?? 0;
            $item->INSTAMBAH = optional($instTambah->firstWhere('kolok', $item->id_kolok))->INSTAMBAH ?? 0;
            $item->AUDITTAMBAH = optional($auditTambah->firstWhere('kolok', $item->id_kolok))->AUDITTAMBAH ?? 0;
            $item->SMT1TAMBAH = optional($smt1Tambah->firstWhere('kolok', $item->id_kolok))->SMT1TAMBAH ?? 0;
            $item->SMT2TAMBAH = optional($smt2Tambah->firstWhere('kolok', $item->id_kolok))->SMT2TAMBAH ?? 0;
        
            $item->REVIEWKURANG = optional($reviewKurang->firstWhere('idkolok', $item->id_kolok))->REVIEWKURANG ?? 0;
            $item->INSKURANG = optional($insKurang->firstWhere('idkolok', $item->id_kolok))->INSKURANG ?? 0;
            $item->AUDITKURANG = optional($auditKurang->firstWhere('idkolok', $item->id_kolok))->AUDITKURANG ?? 0;
            $item->SMT1KURANG = optional($smt1Kurang->firstWhere('idkolok', $item->id_kolok))->SMT1KURANG ?? 0;
            $item->SMT2KURANG = optional($smt2Kurang->firstWhere('idkolok', $item->id_kolok))->SMT2KURANG ?? 0;
        
            // Menambahkan total dari semua data
            $item->Total_SPPB_BAST = $item->SPPB1 + $item->SPPB2 + $item->SPPB3 + $item->SPPB4 +
                $item->BASTSPPB1 + $item->BASTSPPB2 + $item->BASTSPPB3 + $item->BASTSPPB4 +
                $item->BASTPHK3 + $item->BASTHIBAH + $item->BASTTRANSFER +
                $item->REVIEWTAMBAH + $item->INSTAMBAH + $item->AUDITTAMBAH + 
                $item->SMT1TAMBAH + $item->SMT2TAMBAH +
                $item->REVIEWKURANG + $item->INSKURANG + $item->AUDITKURANG + 
                $item->SMT1KURANG + $item->SMT2KURANG;
        
            return $item;
        });

        $selesaiCount = 0;
        $belumCount = 0;

        foreach ($mergedData as $item) {
            if  ($item->upb_sekolah !== 'Y' && $item->flag_blud !== 'Y') {
                if (
                    ($item->Total_SPPB_BAST == 0) &&
                    ($item->tglba_fisik !== 'No Data Found' && !is_null($item->tglba_fisik)) &&
                    ($item->periode_baso !== 'No Data Found' && !is_null($item->periode_baso))
                ) {
                    $selesaiCount++;
                } else {
                    $belumCount++;
                }
            }
        }

        $sekolahSudah = 0;
        $sekolahBelum = 0;

        foreach ($mergedData as $item) {
            if  ($item->upb_sekolah == 'Y') {
                if (
                    ($item->Total_SPPB_BAST == 0) &&
                    ($item->tglba_fisik !== 'No Data Found' && !is_null($item->tglba_fisik)) &&
                    ($item->periode_baso !== 'No Data Found' && !is_null($item->periode_baso))
                ) {
                    $sekolahSudah++;
                } else {
                    $sekolahBelum++;
                }
            }
        }

        $bludSudah = 0;
        $bludBelum = 0;

        foreach ($mergedData as $item) {
            if  ($item->flag_blud == 'Y') {
                if (
                    ($item->Total_SPPB_BAST == 0) &&
                    ($item->tglba_fisik !== 'No Data Found' && !is_null($item->tglba_fisik)) &&
                    ($item->periode_baso !== 'No Data Found' && !is_null($item->periode_baso))
                ) {
                    $bludSudah++;
                } else {
                    $bludBelum++;
                }
            }
        }
        
        return view('Backend.home', [
            'mergedData' => $mergedData,
            'tahunList' => $tahunList,
            'listBulan' => $listBulan,
            'selesaiCount' => $selesaiCount, 
            'belumCount' => $belumCount,
            'sekolahSudah' => $sekolahSudah, 
            'sekolahBelum' => $sekolahBelum,
            'bludSudah' => $bludSudah, 
            'bludBelum' => $bludBelum,
            'user' => $user
        ]);
    }  

    public function notFound()
    {
        return view('Backend.notfound');
    }
}