<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class RekonbkuController extends Controller
{
    public function store(Request $request)
    {
        $tahun = '2025';
        $username = 'bpad';
        $password = 'bp4d';
        $dataCount = 0;
        $maxData = 10000;

        $kodeSkpdList = DB::connection('sqlsrv')->table('master_profile_detail')
            ->where('tahun', $tahun)
            ->where('sts', '1')
            ->whereNull('upb_sekolah')
            ->select('id_kolok', 'nalok', 'id_kolokskpd', 'KODE_SKPD')
            ->distinct()
            ->get();

        $akunBA = DB::connection('sqlsrv_2')
            ->table('glo_katbrg')
            ->where('flag_ba', 1)
            ->pluck('kd_akun')
            ->map(fn($val) => trim((string) $val))
            ->toArray();
        $akunBA = array_flip($akunBA);

        // Ambil data yang sudah direkon
        $dbRekonPairs = DB::connection('sqlsrv_2')
            ->table("rekonbku_detail$tahun")
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

        $insertData = [];

        foreach ($kodeSkpdList as $skpd) {
            if ($dataCount >= $maxData) break;

            $response = Http::withBasicAuth($username, $password)
                ->withOptions(['verify' => false])
                ->get('https://soadki.jakarta.go.id/rest/gov/dki/sipkd/realisasipernobukti2/ws', [
                    'skpd' => $skpd->KODE_SKPD,
                    'tahun' => $tahun,
                ]);

            if ($response->successful()) {
                $apiResults = $response->json()['results'] ?? [];

                foreach ($apiResults as $item) {
                    if ($dataCount >= $maxData) break 2;

                    $noBku = trim((string) ($item['I_BKUNO'] ?? ''));
                    $noBukti = trim((string) ($item['I_DOC_BUKTI'] ?? ''));
                    $realisasi = number_format((float) ($item['REALISASI'] ?? 0), 2, '.', '');
                    $kodeAkun = trim((string) ($item['KODE_AKUN'] ?? ''));
                    $key = $noBku . '|' . $realisasi . '|' . $noBukti;

                    // Default status
                    $rekonStatus = 'Belum Direkon';

                    // Cek status rekon berdasarkan 3 kunci
                    if ($noBku && $realisasi && $noBukti && isset($dbRekonPairs[$key]) && $dbRekonPairs[$key] > 0) {
                        $rekonStatus = 'Sudah Direkon';
                        $dbRekonPairs[$key]--;
                    }

                    // Simpan hanya jika termasuk BA dan sudah direkon
                    if ($rekonStatus === 'Sudah Direkon' && isset($akunBA[$kodeAkun])) {
                        $insertData[] = [
                            'id_kolok'       => $skpd->id_kolok,
                            'nalok'          => $skpd->nalok,
                            'idskpd'         => $item['KODE_SKPD'] ?? '',
                            'tgl_post'       => $item['D_POSTING'] ?? '',
                            'kode_rekening'  => $kodeAkun,
                            'realisasi'      => $realisasi,
                            'status_rekon'   => $rekonStatus,
                            'flag_ba'        => 1,
                            'no_bku'         => $noBku,
                            'no_bukti'       => $noBukti,
                        ];
                        $dataCount++;
                    }
                }
            }
        }

        foreach (array_chunk($insertData, 200) as $chunk) {
            DB::connection('sqlsrv_3')->table("rekon_bku")->insert($chunk);
        }

        return count($insertData) . " data yang sudah direkon berhasil ditambahkan.";
    }

    public function update(Request $request)
    {
        $tahun = '2025';
        $username = 'bpad';
        $password = 'bp4d';
        $dataCount = 0;
        $maxData = 10000;

        $kodeSkpdList = DB::connection('sqlsrv')->table('master_profile_detail')
            ->where('tahun', $tahun)
            ->where('sts', '1')
            ->whereNull('upb_sekolah')
            ->select('id_kolok', 'nalok', 'id_kolokskpd', 'KODE_SKPD')
            ->distinct()
            ->get();

        $akunBA = DB::connection('sqlsrv_2')
            ->table('glo_katbrg')
            ->where('flag_ba', 1)
            ->pluck('kd_akun')
            ->map(fn($val) => trim((string) $val))
            ->toArray();
        $akunBA = array_flip($akunBA);

        // Ambil data yang sudah direkon
        $dbRekonPairs = DB::connection('sqlsrv_2')
            ->table("rekonbku_detail$tahun")
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

        $insertData = [];

        foreach ($kodeSkpdList as $skpd) {
            if ($dataCount >= $maxData) break;

            $response = Http::withBasicAuth($username, $password)
                ->withOptions(['verify' => false])
                ->get('https://soadki.jakarta.go.id/rest/gov/dki/sipkd/realisasipernobukti2/ws', [
                    'skpd' => $skpd->KODE_SKPD,
                    'tahun' => $tahun,
                ]);

            if ($response->successful()) {
                $apiResults = $response->json()['results'] ?? [];

                foreach ($apiResults as $item) {
                    if ($dataCount >= $maxData) break 2;

                    $noBku = trim((string) ($item['I_BKUNO'] ?? ''));
                    $noBukti = trim((string) ($item['I_DOC_BUKTI'] ?? ''));
                    $realisasi = number_format((float) ($item['REALISASI'] ?? 0), 2, '.', '');
                    $kodeAkun = trim((string) ($item['KODE_AKUN'] ?? ''));
                    $key = $noBku . '|' . $realisasi . '|' . $noBukti;

                    // Cek jika sudah direkon dan flag_ba == 1
                    if (isset($dbRekonPairs[$key]) && $dbRekonPairs[$key] > 0 && isset($akunBA[$kodeAkun])) {
                        // Cek apakah data sudah ada di tabel rekon_bku
                        $exists = DB::connection('sqlsrv_3')->table("rekon_bku")
                            ->where('no_bku', $noBku)
                            ->where('no_bukti', $noBukti)
                            ->where('realisasi', $realisasi)
                            ->where('kode_rekening', $kodeAkun)
                            ->exists();

                        if (!$exists) {
                            $insertData[] = [
                                'id_kolok'       => $skpd->id_kolok,
                                'nalok'          => $skpd->nalok,
                                'idskpd'         => $item['KODE_SKPD'] ?? '',
                                'tgl_post'       => Carbon::now(),
                                'kode_rekening'  => $kodeAkun,
                                'realisasi'      => $realisasi,
                                'status_rekon'   => 'Sudah Direkon',
                                'flag_ba'        => 1,
                                'no_bku'         => $noBku,
                                'no_bukti'       => $noBukti,
                            ];
                            $dataCount++;
                        }
                        // Jika sudah ada, skip
                        $dbRekonPairs[$key]--;
                    }
                }
            }
        }

        DB::connection('sqlsrv_3')->table("rekon_bku")->insert($insertData);

        return count($insertData) . " data sudah direkon berhasil diupdate.";
    }

    public function storeBelumRekon(Request $request)
    {
        $tahun = '2025';
        $username = 'bpad';
        $password = 'bp4d';
        $dataCount = 0;
        $maxData = 10000;

        $kodeSkpdList = DB::connection('sqlsrv')->table('master_profile_detail')
            ->where('tahun', $tahun)
            ->where('sts', '1')
            ->whereNull('upb_sekolah')
            ->select('id_kolok', 'nalok', 'id_kolokskpd', 'KODE_SKPD')
            ->distinct()
            ->get();

        // Ambil akun dengan flag_ba = 1
        $akunBA = DB::connection('sqlsrv_2')
            ->table('glo_katbrg')
            ->where('flag_ba', 1)
            ->pluck('kd_akun')
            ->map(fn($val) => trim((string) $val))
            ->toArray();
        $akunBA = array_flip($akunBA);

        // Ambil data yang SUDAH direkon dari rekonbku_detail
        $dbRekonPairs = DB::connection('sqlsrv_2')
            ->table("rekonbku_detail$tahun")
            ->select('no_bku', 'realisasi', 'no_bukti', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('no_bku', 'realisasi', 'no_bukti')
            ->get()
            ->reduce(function ($carry, $item) {
                $key = trim((string) $item->no_bku) . '|' .
                    number_format((float) $item->realisasi, 2, '.', '') . '|' .
                    trim((string) $item->no_bukti);
                $carry[$key] = $item->jumlah;
                return $carry;
            }, []);

        $insertData = [];

        foreach ($kodeSkpdList as $skpd) {
            if ($dataCount >= $maxData) break;

            $response = Http::withBasicAuth($username, $password)
                ->withOptions(['verify' => false, 'timeout' => 30, 'connect_timeout' => 10])
                ->get('https://soadki.jakarta.go.id/rest/gov/dki/sipkd/realisasipernobukti2/ws', [
                    'skpd' => $skpd->KODE_SKPD,
                    'tahun' => $tahun,
                ]);

            if ($response->successful()) {
                $results = $response->json()['results'] ?? [];

                foreach ($results as $item) {
                    if ($dataCount >= $maxData) break 2;

                    $noBku = trim((string) ($item['I_BKUNO'] ?? ''));
                    $realisasi = number_format((float) ($item['REALISASI'] ?? 0), 2, '.', '');
                    $noBukti = trim((string) ($item['I_DOC_BUKTI'] ?? ''));
                    $kodeAkun = trim((string) ($item['KODE_AKUN'] ?? ''));
                    $key = $noBku . '|' . $realisasi . '|' . $noBukti;

                    // Hanya proses jika kode akun adalah bagian dari BA
                    if (!isset($akunBA[$kodeAkun])) {
                        continue;
                    }

                    // Simpan hanya jika BELUM DIREKON
                    if (!(isset($dbRekonPairs[$key]) && $dbRekonPairs[$key] > 0)) {
                        $insertData[] = [
                            'id_kolok'       => $skpd->id_kolok,
                            'nalok'          => $skpd->nalok,
                            'idskpd'         => $item['KODE_SKPD'] ?? '',
                            'tgl_post'       => $item['D_POSTING'] ?? '',
                            'kode_rekening'  => $kodeAkun,
                            'realisasi'      => $realisasi,
                            'status_rekon'   => 'Belum Direkon',
                            'flag_ba'        => 1,
                            'no_bku'         => $noBku,
                            'no_bukti'       => $noBukti,
                        ];
                        $dataCount++;
                    } else {
                        // Kalau sudah direkon, kurangi jumlahnya (biar cocok satu lawan satu)
                        $dbRekonPairs[$key]--;
                    }
                }
            }
        }

        foreach (array_chunk($insertData, 200) as $chunk) {
            DB::connection('sqlsrv_3')->table("rekon_bku_belum")->insert($chunk);
        }

        return count($insertData) . " data 'Belum Direkon' berhasil ditambahkan.";
    }

    public function updateBelumRekon(Request $request)
    {
        $tahun = '2025';
        $username = 'bpad';
        $password = 'bp4d';
        $dataCount = 0;
        $maxData = 10000;

        $kodeSkpdList = DB::connection('sqlsrv')->table('master_profile_detail')
            ->where('tahun', $tahun)
            ->where('sts', '1')
            ->whereNull('upb_sekolah')
            ->select('id_kolok', 'nalok', 'id_kolokskpd', 'KODE_SKPD')
            ->distinct()
            ->get();

        $akunBA = DB::connection('sqlsrv_2')
            ->table('glo_katbrg')
            ->where('flag_ba', 1)
            ->pluck('kd_akun')
            ->map(fn($val) => trim((string) $val))
            ->toArray();
        $akunBA = array_flip($akunBA);

        // Ambil data yang sudah direkon
        $dbRekonPairs = DB::connection('sqlsrv_2')
            ->table("rekonbku_detail$tahun")
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

        $insertData = [];

        foreach ($kodeSkpdList as $skpd) {
            if ($dataCount >= $maxData) break;

            $response = Http::withBasicAuth($username, $password)
                ->withOptions(['verify' => false])
                ->get('https://soadki.jakarta.go.id/rest/gov/dki/sipkd/realisasipernobukti2/ws', [
                    'skpd' => $skpd->KODE_SKPD,
                    'tahun' => $tahun,
                ]);

            if ($response->successful()) {
                $apiResults = $response->json()['results'] ?? [];

                foreach ($apiResults as $item) {
                    if ($dataCount >= $maxData) break 2;

                    $noBku = trim((string) ($item['I_BKUNO'] ?? ''));
                    $noBukti = trim((string) ($item['I_DOC_BUKTI'] ?? ''));
                    $realisasi = number_format((float) ($item['REALISASI'] ?? 0), 2, '.', '');
                    $kodeAkun = trim((string) ($item['KODE_AKUN'] ?? ''));
                    $key = $noBku . '|' . $realisasi . '|' . $noBukti;

                    // Hanya untuk akun yang flag_ba = 1
                    if (!isset($akunBA[$kodeAkun])) continue;

                    // Jika belum direkon (tidak ada di $dbRekonPairs)
                    if (!(isset($dbRekonPairs[$key]) && $dbRekonPairs[$key] > 0)) {
                        // Cek apakah data belum pernah disimpan
                        $exists = DB::connection('sqlsrv_3')->table("rekon_bku")
                            ->where('no_bku', $noBku)
                            ->where('no_bukti', $noBukti)
                            ->where('realisasi', $realisasi)
                            ->where('kode_rekening', $kodeAkun)
                            ->exists();

                        if (!$exists) {
                            $insertData[] = [
                                'id_kolok'       => $skpd->id_kolok,
                                'nalok'          => $skpd->nalok,
                                'idskpd'         => $item['KODE_SKPD'] ?? '',
                                'tgl_post'       => Carbon::now(),
                                'kode_rekening'  => $kodeAkun,
                                'realisasi'      => $realisasi,
                                'status_rekon'   => 'Belum Direkon',
                                'flag_ba'        => 1,
                                'no_bku'         => $noBku,
                                'no_bukti'       => $noBukti,
                            ];
                            $dataCount++;
                        }
                    }
                }
            }
        }

       foreach (array_chunk($insertData, 200) as $chunk) {
            DB::connection('sqlsrv_3')->table("rekon_bku_belum")->insert($chunk);
        }

        return count($insertData) . " data belum direkon berhasil diupdate.";
    }

}
