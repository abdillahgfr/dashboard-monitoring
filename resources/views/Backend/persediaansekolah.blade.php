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

                            <h2 class="card-title">Laporan Sistem Persediaan Sekolah - April {{ now()->year }}</h2>
                        </header>
                        <div class="card-body">
                            {{-- <form method="GET" action="{{ url('/notifikasi') }}">
                                <div class="form-group">
                                    <select name="tahun" class="form-control">
                                        <option value="">Pilih Tahun</option>
                                        @foreach ($tahunList as $year)
                                            <option value="{{ $year }}"
                                                {{ request('tahun') == $year ? 'selected' : '' }}>{{ $year }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <select name="smt" class="form-control mt-2">
                                        <option value="">Pilih Bulan</option>
                                        @foreach ($listBulan as $bulan)
                                            <option value="{{ is_array($bulan) && array_key_exists('value', $bulan) ? $bulan['value'] : $bulan }}"
                                                {{ request('smt') == (is_array($bulan) && array_key_exists('value', $bulan) ? $bulan['value'] : $bulan) ? 'selected' : '' }}>
                                                {{ is_array($bulan) && array_key_exists('label', $bulan) ? $bulan['label'] : $bulan }}
                                            </option>
                                        @endforeach
                                    </select>
                                   
                                    <button type="submit" class="btn btn-primary mt-2">Filter</button>
                                    <a href="{{ url('/notifikasi') }}" class="btn btn-secondary mt-2">Clear</a>
                                </div>
                            </form> --}}
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
                                        <th>Rekon BKU Selesai</th>
                                        <th>Status</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($mergedData as $item)
                                        @if ($item->upb_sekolah === 'Y')  <!-- Show only SEKOLAH -->
                                            <tr>
                                                <td>{{ $item->id_kolok ?? 'No Kolok Found' }}</td>
                                                <td>SEKOLAH</td>
                                                <td>{{ $item->nalok ?? 'No Nalok Found' }}</td>
                                                <td>{{ $item->tahun ?? 'No Year Found' }}</td>
                                                <td>
                                                    {{ $item->Total_SPPB_BAST ?? '0' }}
                                                </td>
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
                                                <td>
                                                    Sudah: {{ $statusRekon[$item->id_kolok]['sudah_direkon'] ?? 0 }}<br>
                                                    Belum: {{ $statusRekon[$item->id_kolok]['belum_direkon_ba'] ?? 0 }}<br>
                                                    Status: <strong>{{ $statusRekon[$item->id_kolok]['status'] ?? '-' }}</strong>
                                                </td>
                                                <td>
                                                    @php
                                                        $rekonBkuStatus = $statusRekon[$item->id_kolok]['status'] ?? null;

                                                        // Jika salah satu dari kondisi ini TIDAK memenuhi, maka statusnya BELUM
                                                        $isBelum = 
                                                            ($item->Total_SPPB_BAST > 0) || 
                                                            ($item->tglba_fisik === 'No Data Found') || 
                                                            ($item->periode_baso === 'No Data Found') || 
                                                            ($rekonBkuStatus === 'Belum Selesai');
                                                    @endphp

                                                    @if ($isBelum)
                                                        <span class="badge badge-danger">Belum</span>
                                                    @else
                                                        <span class="badge badge-success">Sudah</span>
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
