@extends('Backend.Layout.app') <!-- Extends the layout -->

@section('content')
    <!-- Defines the 'content' section -->
    <section role="main" class="content-body">
        <header class="page-header">
            <h2>Dashboard Persediaan</h2>
        </header>

        <div class="inner-wrapper">
            <div class="row mb-4">
                <div class="col-xl-12">
                    <section class="card">
                        <header class="card-header">
                            <div class="card-actions">
                                <a href="#" class="card-action card-action-toggle" data-card-toggle></a>
                                <a href="#" class="card-action card-action-dismiss" data-card-dismiss></a>
                            </div>

                            <h2 class="card-title">Load Content</h2>
                        </header>
                        <div class="card-body" style="min-height: 150px;">
                            {{-- <ul ic-prepend-from="{{ route('rekonbku.update') }}" ic-poll="10s" ic-limit-children="10">
                                <li>Menunggu Data Rekon...</li>
                            </ul> --}}
                        </div>
                    </section>
                </div>
            </div>
            <!-- start: page -->
            <div class="row">
                <div class="col-xl-12">
                    <section class="card">
                        {{-- <section class="card mb-2">
                            <header class="card-header">
                                <div class="card-actions">
                                    <a href="#" class="card-action card-action-toggle" data-card-toggle=""></a>
                                    <a href="#" class="card-action card-action-dismiss" data-card-dismiss=""></a>
                                </div>

                                <h2 class="card-title">Insert into DB</h2>
                            </header>
                            <div class="card-body">
                                <div class="col-6">
                                    <form action="{{ route('rekonbku.store') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="mb-1 mt-1 me-1 btn btn-primary">
                                            Tambah Data Sudah DiRekon ke Table
                                        </button>
                                    </form>

                                    <form action="{{ route('rekonbku.update') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="mb-1 mt-1 me-1 btn btn-info">
                                            Update Data Sudah DiRekon ke Table
                                        </button>
                                    </form>
                                </div>
                                <hr>
                                <div class="col-6">
                                    <form action="{{ route('rekonbku.belum') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="mb-1 mt-1 me-1 btn btn-primary">
                                            Tambah Data Belum DiRekon ke Table
                                        </button>
                                    </form>
                                    
                                    <form action="{{ route('rekonbku.updatebelum') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="mb-1 mt-1 me-1 btn btn-primary">
                                            Update Data Belum DiRekon ke Table
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </section> --}}

                        <div class="card-body">
                            <table class="table table-responsive-md table-striped mb-0" id="datatable-tabletools" border="1" cellpadding="10" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>ID Kolok</th>
                                        <th>Nalok</th>
                                        <th>Kode SKPD</th>
                                        <th>Tgl Post</th>
                                        <th>Kode Rekening</th>
                                        <th>Realisasi</th>
                                        <th>Status Rekon</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data as $index => $item)
                                        @if(isset($item['akun_ba_status']) && $item['akun_ba_status'] === 'Termasuk BA') <!-- Filter hanya yang flag_ba = 1 -->
                                            <tr>
                                                <td>{{ $item['id_kolok'] ?? '-' }}</td>
                                                <td>{{ $item['nalok'] ?? '-' }}</td>
                                                <td>{{ $item['KODE_SKPD'] ?? 'Tidak ada Kode SKPD' }}</td>
                                                <td>
                                                    @if(!empty($item['D_POSTING']) && strlen($item['D_POSTING']) == 8)
                                                        {{ substr($item['D_POSTING'], 0, 4) }}-{{ substr($item['D_POSTING'], 4, 2) }}-{{ substr($item['D_POSTING'], 6, 2) }}
                                                    @else
                                                        Tidak ada Tgl Post
                                                    @endif
                                                </td>
                                                <td>{{ $item['KODE_AKUN'] ?? '-' }}</td>
                                                <td>
                                                    {{ isset($item['REALISASI']) ? number_format($item['REALISASI'], 2, ',', '.') : 'Tidak ada Realisasi' }}
                                                </td>                                                
                                                <td>
                                                    @if($item['rekon_status'] == 'Sudah Direkon')
                                                        <span class="badge badge-success">Sudah Direkon</span>
                                                    @else
                                                        <span class="badge badge-warning">Belum Direkon</span>
                                                    @endif
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
