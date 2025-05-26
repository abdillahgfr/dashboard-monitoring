@extends('Backend.Layout.app') <!-- Extends the layout -->

@section('content')
    <!-- Defines the 'content' section -->
    <section role="main" class="content-body">
        <header class="page-header">
            <h2>Monitoring Persediaan Semester 2</h2>
        </header>

        <div class="inner-wrapper">
            <div class="row mt-4">
                <div class="col-xl-12">
                    <section class="card">
                        <div class="col-lg-12">
                            <section class="card card-primary mb-4">
                                <header class="card-header">
                                    <div class="card-actions">
                                        <a href="#" class="card-action card-action-toggle" data-card-toggle></a>
                                        <a href="#" class="card-action card-action-dismiss" data-card-dismiss></a>
                                    </div>
                                    <h2 class="card-title">Tabel Monitoring Persediaan Semester 2</h2>
                                    <p class="card-subtitle">Progress Seluruh Wilayah</p>
                                </header>
                                <div class="card-body">
                                    <table class="table table-responsive-md table-striped mb-0" id="datatable-tabletools">
                                        <thead>
                                            <tr>
                                                <th>KOLOK</th>
                                                <th>Flag</th>
                                                <th>NALOK</th>
                                                <th>Tahun</th>
                                                <th>Notifikasi</th>
                                                <th>Stok Opname</th>
                                                <th>BA Stok Fisik</th>
                                                <th>Rekon BKU(Selesai)</th>
                                                <th>Rekon BKU(Belum)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($mergedData as $item)
                                                <tr>
                                                    <td>{{ $item->id_kolok }}</td>
                                                    <td>
                                                        @if ($item->upb_sekolah === 'Y')
                                                            SEKOLAH
                                                        @elseif ($item->flag_blud === 'Y')
                                                            BLUD
                                                        @else
                                                            PD/OPD
                                                        @endif
                                                    </td>
                                                    <td>{{ $item->nalok }}</td>
                                                    <td>{{ $item->tahun }}</td>
                                                    <td class="text-center">{{ $item->Total_SPPB_BAST }}</td>
                                                    <td>
                                                        @php
                                                            $tgl = $item->periode_baso;
                                                            $bulanTerisi = null;
                                                            $belum = [];

                                                            if (!empty($tgl) && $tgl !== 'No Data Found') {
                                                                try {
                                                                    $bulanTerisi = \Carbon\Carbon::createFromFormat('d-m-Y', $tgl)->format('n');
                                                                } catch (\Exception $e) {
                                                                    $bulanTerisi = null;
                                                                }
                                                            }

                                                            // Cek bulan 7 (Juli) sampai 12 (Desember)
                                                            foreach (range(7, 12) as $bulan) {
                                                                if ((int)$bulanTerisi !== $bulan) {
                                                                    $namaBulan = \Carbon\Carbon::create()->month($bulan)->locale('id')->translatedFormat('F');
                                                                    $belum[] = $namaBulan;
                                                                }
                                                            }
                                                        @endphp

                                                        @if (count($belum) === 0)
                                                            <span class="badge badge-success">Sudah (Juli - Desember)</span>
                                                        @else
                                                            @foreach ($belum as $b)
                                                                <div>{{ $b }}: <span class="badge badge-danger">Belum</span></div>
                                                            @endforeach
                                                        @endif
                                                    </td>

                                                    <td>
                                                        @php
                                                            $tgl = $item->tglba_fisik;
                                                            $bulanTerisi = null;
                                                            $belum = [];

                                                            if (!empty($tgl) && $tgl !== 'No Data Found') {
                                                                try {
                                                                    $bulanTerisi = \Carbon\Carbon::createFromFormat('d-m-Y', $tgl)->format('n');
                                                                } catch (\Exception $e) {
                                                                    $bulanTerisi = null;
                                                                }
                                                            }

                                                            // Cek bulan 7 (Juli) sampai 12 (Desember)
                                                            foreach (range(7, 12) as $bulan) {
                                                                if ((int)$bulanTerisi !== $bulan) {
                                                                    $namaBulan = \Carbon\Carbon::create()->month($bulan)->locale('id')->translatedFormat('F');
                                                                    $belum[] = $namaBulan;
                                                                }
                                                            }
                                                        @endphp

                                                        @if (count($belum) === 0)
                                                            <span class="badge badge-success">Sudah (Juli - Desember)</span>
                                                        @else
                                                            @foreach ($belum as $b)
                                                                <div>{{ $b }}: <span class="badge badge-danger">Belum</span></div>
                                                            @endforeach
                                                        @endif
                                                    </td>

                                                    <td class="text-center">
                                                        {{ $item->jumlah_rekon }}
                                                    </td>

                                                    <td class="text-center">
                                                        {{ $item->jumlah_belum_rekon }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </section>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </section>
@endsection
