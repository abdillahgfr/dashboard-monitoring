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

                            <h2 class="card-title">Data Notifikasi - {{ now()->year }}</h2>
                        </header>
                        <div class="card-body">
                            <table class="table table-responsive-md table-striped mb-0" id="datatable-tabletools">
                                <thead>
                                    <tr>
                                        <th>KOLOK</th>
                                        <th>NALOK</th>
                                        <th>Notifikasi </th>
                                        <th>Status</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($master as $item)
                                        <tr>
                                            <td>{{ $item->kolok ?? 'No Kolok Found' }}</td>
                                            <td>{{ $item->nalok ?? 'No Nalok Found' }}</td>
                                            <td>
                                                {{ $item->Total_SPPB_BAST ?? '0' }}
                                            </td>
                                            <td>
                                                @if ($item->Total_SPPB_BAST > 0)
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
            </div>
            <!-- end: page -->
        </div>
    </section>
@endsection
