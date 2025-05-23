@extends('Backend.Layout.app') <!-- Extends the layout -->

@section('content')
    <!-- Defines the 'content' section -->
    <section role="main" class="content-body">
        <header class="page-header">
            <h2>Monitoring Persediaan Semester 1</h2>
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
                <div class="col-xl-4">
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
                </div>
                <div class="col-xl-4">
                    <section class="card card-primary mb-4">
                        <div class="col-lg-12">
                            <section class="card">
                                <header class="card-header">
                                    <div class="card-actions">
                                        <a href="#" class="card-action card-action-toggle" data-card-toggle></a>
                                        <a href="#" class="card-action card-action-dismiss" data-card-dismiss></a>
                                    </div>

                                    <h2 class="card-title">Chart Monitoring Persediaan</h2>
                                    <p class="card-subtitle">Progress SEKOLAH
                                        {{ \Carbon\Carbon::create()->month($bulan)->locale('id')->monthName }}</p>
                                </header>
                                <section class="card card-modern card-big-info">
                                    <div class="card-body">
                                        <!-- Tab 2: SEKOLAH -->
                                        <div class="card-body">
                                            <div class="chart chart-md" id="sekolahPie"></div>
                                            <script type="text/javascript">
                                                var sekolahPieData = [{
                                                        label: "Selesai",
                                                        data: [
                                                            [1, {{ $sekolahSudah }}]
                                                        ],
                                                        color: '#2baab1'
                                                    },
                                                    {
                                                        label: "Belum Selesai",
                                                        data: [
                                                            [1, {{ $sekolahBelum }}]
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
                </div>
                <div class="col-xl-4">
                    <section class="card card-primary mb-4">
                        <div class="col-lg-12">
                            <section class="card">
                                <header class="card-header">
                                    <div class="card-actions">
                                        <a href="#" class="card-action card-action-toggle" data-card-toggle></a>
                                        <a href="#" class="card-action card-action-dismiss" data-card-dismiss></a>
                                    </div>

                                    <h2 class="card-title">Chart Monitoring Persediaan</h2>
                                    <p class="card-subtitle">Progress BLUD
                                        {{ \Carbon\Carbon::create()->month($bulan)->locale('id')->monthName }}</p>
                                </header>
                                <section class="card card-modern card-big-info">
                                    <div class="card-body">
                                        <!-- Tab 3: BLUD -->
                                        <div class="card-body">
                                            <div class="chart chart-md" id="bludPie"></div>
                                            <script type="text/javascript">
                                                var bludPieData = [{
                                                        label: "Selesai",
                                                        data: [
                                                            [1, {{ $bludSudah }}]
                                                        ],
                                                        color: '#2baab1'
                                                    },
                                                    {
                                                        label: "Belum Selesai",
                                                        data: [
                                                            [1, {{ $bludBelum }}]
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
                </div>
            </div>
            <!-- end: page -->

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

                                    <h2 class="card-title">Tabel Monitoring Persediaan
                                        {{ \Carbon\Carbon::create()->month($bulan)->locale('id')->monthName }}</h2>
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
                                                <th>Progress</th>
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
                                                    <td class="text-center">
                                                        @php
                                                            // Ambil bulan yang dipilih dari request
                                                            $selectedMonth = (int) request('bulan', $bulan);

                                                            // Jika bulan pada data <= bulan yang dipilih, tampilkan nilainya, jika tidak tampilkan 0
                                                            // Asumsi $item->bulan adalah field bulan pada data, jika tidak ada, sesuaikan dengan field yang benar
                                                            if (isset($item->bulan)) {
                                                                echo ($item->bulan <= $selectedMonth) ? $item->Total_SPPB_BAST : 0;
                                                            } else {
                                                                // Jika tidak ada field bulan, tampilkan langsung (default behavior)
                                                                echo $item->Total_SPPB_BAST;
                                                            }
                                                        @endphp
                                                    </td>
                                                    <td>
                                                        @if ($item->periode_baso === 'No Data Found' || is_null($item->periode_baso))
                                                            <span class="badge badge-danger">Belum</span>
                                                        @else
                                                            <span class="badge badge-success">Sudah</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($item->tglba_fisik === 'No Data Found' || is_null($item->tglba_fisik))
                                                            <span class="badge badge-danger">Belum</span>
                                                        @else
                                                            <span class="badge badge-success">Sudah</span>
                                                        @endif
                                                    </td>
                                                        
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
                                                            if ($jumlahBelumRekon == 0 && $jumlahRekon >= 0 ) {
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
