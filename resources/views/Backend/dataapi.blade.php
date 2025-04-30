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
                <div class="col-xl-12">
                    <section class="card">
                        <header class="card-header card-header-transparent">
                            <div class="card-actions">
                                <a href="#" class="card-action card-action-toggle" data-card-toggle></a>
                            </div>

                            <h2 class="card-title">API Rekon BKU</h2>
                        </header>
                        <div class="card-body">
                            <table class="table table-responsive-md table-striped mb-0" id="datatable-tabletools" border="1" cellpadding="10" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>No Bukti</th>
                                        <th>Kode SKPD</th>
                                        <th>Realisasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data as $index => $item)
                                        <tr>
                                            <td>{{ $item['I_DOC_BUKTI'] ?? '-' }}</td>
                                            <td>{{ $item['KODE_SKPD'] ?? 'Tidak ada Kode SKPD' }}</td>
                                            <td>{{ $item['REALISASI'] ?? '-' }}</td>
                                        </tr>
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
