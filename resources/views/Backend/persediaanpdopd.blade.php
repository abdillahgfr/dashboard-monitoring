@extends('Backend.Layout.app') <!-- Extends the layout -->

@section('content')
    <!-- Defines the 'content' section -->
    <section role="main" class="content-body">
        <header class="page-header">
            <h2>Dashboard Persediaan</h2>
        </header>

        <div class="inner-wrapper">
            <!-- start: page -->
            <div class="row">
                <form method="GET" action="{{ url()->current() }}" class="mb-3">
                    <div class="form-group">
                        <label for="bulan">Pilih Bulan:</label>
                        <select name="bulan" id="bulan" class="form-control" onchange="this.form.submit()">
                            @foreach(range(1,12) as $num)
                                <option value="{{ $num }}" {{ $bulan == $num ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($num)->locale('id')->monthName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
                <section class="card card-primary mb-4">
                    <div class="col-lg-12">
                        <section class="card">
                            <header class="card-header">
                                <div class="card-actions">
                                    <a href="#" class="card-action card-action-toggle" data-card-toggle></a>
                                    <a href="#" class="card-action card-action-dismiss" data-card-dismiss></a>
                                </div>
                                <h2 class="card-title">Chart Monitoring Persediaan</h2>
                                <p class="card-subtitle">Progress PD/OPD
                                    {{ \Carbon\Carbon::create()->month($bulan)->locale('id')->monthName }}</p>
                            </header>
                            <section class="card card-modern card-big-info">
                                <div class="card-body">
                                    <!-- Tab 1: PD/OPD (active on load) -->
                                    <div class="card-body">
                                        <div class="chart chart-md" id="flotPie"></div>
                                        <script type="text/javascript">
                                            var flotPieData = [{
                                                    label: "Selesai",
                                                    data: [
                                                        [1, {{ $selesaiCount }}]
                                                    ],
                                                    color: '#2baab1'
                                                },
                                                {
                                                    label: "Belum Selesai",
                                                    data: [
                                                        [1, {{ $belumCount }}]
                                                    ],
                                                    color: '#E36159'
                                                }
                                            ];
                                        </script>
                                    </div>
                                </div>
                            </section>
                        </section>
                    </div>
                </section>
                <div class="col-xl-12">
                    <section class="card">
                        <header class="card-header card-header-transparent">
                            <div class="card-actions">
                                <a href="#" class="card-action card-action-toggle" data-card-toggle></a>
                            </div>

                            <h2 class="card-title">Laporan Sistem Persediaan PD/OPD - April {{ now()->year }}</h2>
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
                                        <th>No BA Fisik</th>
                                        <th>Rekon BKU (Selesai)</th>
                                        <th>Rekon BKU (Belum)</th>
                                        <th>Status</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($mergedData as $item)
                                        @if ($item->upb_sekolah !== 'Y' && $item->flag_blud !== 'Y')  <!-- Show only PD/OPD -->
                                            <tr>
                                                <td>{{ $item->id_kolok ?? 'No Kolok Found' }}</td>
                                                <td>PD/OPD</td>
                                                <td>{{ $item->nalok ?? 'No Nalok Found' }}</td>
                                                <td>{{ $item->tahun ?? 'No Year Found' }}</td>
                                                <td>{{ $item->Total_SPPB_BAST ?? '0' }}</td>
                                                <td>
                                                    {{ $item->periode_baso ?? 'No SO Found' }}
                                                    @if ($item->periode_baso === 'No Data Found' || is_null($item->periode_baso))
                                                        <span class="badge badge-danger">Belum</span>
                                                    @else
                                                        <span class="badge badge-success">Sudah</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $item->tglba_fisik ?? 'No BASO Found' }}
                                                    @if ($item->tglba_fisik === 'No Data Found' || is_null($item->tglba_fisik))
                                                        <span class="badge badge-danger">Belum</span>
                                                    @else
                                                        <span class="badge badge-success">Sudah</span>
                                                    @endif
                                                </td>
                                                <td>{{ $item->no_bafisik ?? 'No BA Fisik Found' }}</td>
                                                <td class="text-center">
                                                        {{ $item->jumlah_rekon }}
                                                    </td>

                                                    <td class="text-center">
                                                        {{ $item->jumlah_belum_rekon }}
                                                    </td>
                                                <td>
                                                    @php
                                                        // Status Rekon BKU berdasarkan jumlah rekonsiliasi
                                                        $jumlahRekon = $item->jumlah_rekon ?? 0;
                                                        $jumlahBelumRekon = $item->jumlah_belum_rekon ?? 0;

                                                        $conditionsMet = 0;

                                                        if ($item->Total_SPPB_BAST == 0) {
                                                            $conditionsMet++;
                                                        }
                                                        if (!is_null($item->tglba_fisik) && $item->tglba_fisik !== 'No Data Found') {
                                                            $conditionsMet++;
                                                        }
                                                        if (!is_null($item->periode_baso) && $item->periode_baso !== 'No Data Found') {
                                                            $conditionsMet++;
                                                        }

                                                        // Kondisi keempat: jika belum_rekon = 0 maka dianggap selesai
                                                        if ($jumlahBelumRekon == 0 && $jumlahRekon > 0 | $jumlahRekon == 0) {
                                                            $conditionsMet++;
                                                        }

                                                        $maxConditions = 4;
                                                        $progress = round(($conditionsMet / $maxConditions) * 100, 2);
                                                    @endphp

                                                    <div class="progress progress-sm progress-half-rounded m-0 mt-1 light">
                                                        <div class="progress-bar 
                                                            {{ $progress == 100 ? 'progress-bar-success' : ($progress >= 50 ? 'progress-bar-warning' : 'progress-bar-danger') }}"
                                                            role="progressbar" aria-valuenow="{{ $progress }}"
                                                            aria-valuemin="0" aria-valuemax="100"
                                                            style="width: {{ $progress }}%;">
                                                            {{ $progress }}%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>                                
                            </table>
                        </div>
                    </section>
                </div>
            </div>
            <!-- end: page -->
        </div>
    </section>
@endsection
