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
                        <label for="bulan">Pilih Bulan:</label><br>
                        <select name="bulan" id="bulan" class="mb-1 mt-1 me-1 btn btn-default dropdown-toggle show" onchange="this.form.submit()">
                            @foreach(range(1,12) as $num)
                                <option value="{{ $num }}" {{ $bulan == $num ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($num)->locale('id')->monthName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
                <div class="col-xl-12">
                    <section class="card">
                        <header class="card-header card-header-transparent">
                            <div class="card-actions">
                                <a href="#" class="card-action card-action-toggle" data-card-toggle></a>
                            </div>

                            <h2 class="card-title">Laporan Sistem Persediaan SEKOLAH -  {{ \Carbon\Carbon::create()->month($bulan)->locale('id')->monthName }} 2025</h2>
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
