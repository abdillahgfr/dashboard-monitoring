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
        $tahun = $request->get('tahun'); // Retrieve the 'tahun' filter from the request

        $bpadmasterData = DB::connection('sqlsrv')->table('master_profile')
            ->join('master_profile_detail', 'master_profile.id_kolok', '=', 'master_profile_detail.id_kolok')
            ->where('master_profile_detail.tahun', $tahun) // Filter data for the year 2025
            ->select('master_profile.id_kolok', 'master_profile.nalok', 'master_profile_detail.tahun', 'master_profile_detail.upb_sekolah', 'master_profile_detail.flag_blud')
            ->get();

        // Query from the second database
        $currentYear = $tahun ?? date('Y'); // Use the selected year or default to the current year
        
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
        
        // Dynamically determine the table name based on the year
        $tableName = 'so_data' . $currentYear;

        $bpadinventoryData = DB::connection('sqlsrv_2')->table($tableName)
            ->whereYear('periode_baso', $currentYear) // Filter by the selected year
            ->whereMonth('periode_baso', $currentMonth) // Filter by the selected or current month
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

        // Apply filter by 'tahun', if specified
        if ($tahun) {
            $bpadmasterData->where('master_profile_detail.tahun', $tahun); // Show the selected year
        } else {
            $currentYear = date('Y');
            $bpadmasterData->where('master_profile_detail.tahun', $currentYear); // Default to the current year (e.g., 2025)
        }

        // Fetch a distinct list of available 'tahun' values for the dropdown
        $tahunList = DB::table('master_profile_detail')
            ->distinct() // Get unique 'tahun' values
            ->pluck('tahun');

        // Map the data to include the flag information
        $flagList = $mergedData->map(function ($item) {
            if ($item->upb_sekolah === 'Y') {
            $item->flag = 'SEKOLAH';
            } elseif ($item->flag_blud === 'Y') {
            $item->flag = 'BLUD';
            } else {
            $item->flag = 'PD/OPD';
            }
            return $item;
        });

        // Apply filter by 'flag', if specified
        $flag = $request->get('flag');
        if ($flag) {
            $mergedData = $mergedData->filter(function ($item) use ($flag) {
            return $item->flag === $flag;
            });
        }

        // Extract unique smt values for the dropdown
        $smtList = $bpadinventoryData->pluck('smt')->unique()->filter(function ($smt) {
            return $smt !== 'No Data Found'; // Exclude 'No Data Found' from the list
        })->values();

        // Pass the data and list of years to the view
        return view('Backend.home', compact('mergedData', 'tahunList', 'flagList', 'listBulan'));
    }
    

    public function getData()
    {
        // Ambil master data dari sqlsrv
        $master = DB::connection('sqlsrv')->table('master_profile_detail')
        ->where('sts', '1')
        ->where('tahun', '2025')
        ->select('kolok', 'nalok')
        ->get();

        // Ambil data SPPB4 dari sqlsrv_2
        $sppb4 = DB::connection('sqlsrv_2')->table('rq_data2025')
        ->select('idskpd', DB::raw('COUNT(*) AS SPPB4'))
        ->whereRaw("SUBSTRING(noref, 8, 5) = 'RQ2.5'")
        ->whereYear('tgl_rq', '2025')
        ->whereNull('stat_rq')
        ->where('sts', '1')
        ->groupBy('idskpd')
        ->get();

        // Gabungkan manual di PHP
        $master = $master->map(function ($item) use ($sppb4) {
        $match = $sppb4->firstWhere('idskpd', $item->kolok);
        $item->SPPB4 = $match ? $match->SPPB4 : 0;
        return $item;
        });

        return response()->json($master);

    }
       

}