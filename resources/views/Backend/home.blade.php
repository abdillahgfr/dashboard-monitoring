@extends('Backend.Layout.app') <!-- Extends the layout -->

@section('content')
    <!-- Defines the 'content' section -->
    <section role="main" class="content-body">
        <header class="page-header">
            <h2>Dashboard</h2>
        </header>

        <div class="inner-wrapper">

            <div class="alert alert-primary alert-dismissible fade show" role="alert">
                <strong>Welcome !</strong> Hi, {{ $user['nmunit'] }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true" aria-label="Close"></button>
            </div>
            <!-- start: page -->
            <div class="row">
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
                                    <p class="card-subtitle">Progress PD/OPD April</p>
                                </header>
                                <section class="card card-modern card-big-info">
                                    <div class="card-body">
                                        <!-- Tab 1: PD/OPD (active on load) -->
                                        <div class="card-body">
                                            <div class="chart chart-md" id="flotPie"></div>
                                            <script type="text/javascript">
                                                var flotPieData = [
                                                    { label: "Selesai", data: [[1, {{ $selesaiCount }}]], color: '#2baab1' },
                                                    { label: "Belum Selesai", data: [[1, {{ $belumCount }}]], color: '#E36159' }
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
                                    <p class="card-subtitle">Progress SEKOLAH April</p>
                                </header>
                                <section class="card card-modern card-big-info">
                                    <div class="card-body">
                                        <!-- Tab 2: SEKOLAH -->
                                        <div class="card-body">
                                            <div class="chart chart-md" id="sekolahPie"></div>
                                            <script type="text/javascript">
                                                var sekolahPieData = [
                                                    { label: "Selesai", data: [[1, {{ $sekolahSudah }}]], color: '#2baab1' },
                                                    { label: "Belum Selesai", data: [[1, {{ $sekolahBelum }}]], color: '#E36159' }
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
                                    <p class="card-subtitle">Progress BLUD April</p>
                                </header>
                                <section class="card card-modern card-big-info">
                                    <div class="card-body">
                                        <!-- Tab 3: BLUD -->
                                        <div class="card-body">
                                            <div class="chart chart-md" id="bludPie"></div>
                                            <script type="text/javascript">
                                                var bludPieData = [
                                                    { label: "Selesai", data: [[1, {{ $bludSudah }}]], color: '#2baab1' },
                                                    { label: "Belum Selesai", data: [[1, {{ $bludBelum }}]], color: '#E36159' }
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

                                    <h2 class="card-title">Tabel Monitoring Persediaan April</h2>
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
                                                <th>Rekon BKU</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($mergedData as $item)
                                                <tr>
                                                    <td>{{ $item->id_kolok }}</td>
                                                    <td>
                                                        @if ($item->upb_sekolah === 'Y') SEKOLAH
                                                        @elseif ($item->flag_blud === 'Y') BLUD
                                                        @else PD/OPD @endif
                                                    </td>
                                                    <td>{{ $item->nalok }}</td>
                                                    <td>{{ $item->tahun }}</td>
                                                    <td>{{ $item->Total_SPPB_BAST }}</td>
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
                                                    <td></td>
                                                    <td>
                                                        @if ($item->Total_SPPB_BAST > 0 || $item->tglba_fisik === 'No Data Found' || $item->periode_baso === 'No Data Found')
                                                            <span class="badge badge-danger">Belum</span>
                                                        @else
                                                            <span class="badge badge-success">Sudah</span>
                                                        @endif
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
