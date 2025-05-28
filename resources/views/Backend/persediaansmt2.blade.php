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
                                                            $bulanIndo = [7=>'Jul',8=>'Aug',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
                                                            $listPeriode = collect($item->all_periode_baso ?? []);
                                                        @endphp

                                                        @foreach ($bulanIndo as $bulan => $nama)
                                                            @php
                                                                $match = $listPeriode->first(function ($tanggal) use ($bulan) {
                                                                    return $tanggal && \Carbon\Carbon::parse($tanggal)->month === $bulan;
                                                                });
                                                            @endphp
                                                            {!! $nama . ' : ' . ($match ? '<span style="color:green;">✅</span>' : '<span style="color:red;">❌</span>') !!}<br>
                                                        @endforeach
                                                    </td>
                                                    <td>
                                                        @php
                                                            $bulanIndo = [7=>'Jul',8=>'Aug',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
                                                            $listPeriode = collect($item->all_periodeba_fisik ?? []);
                                                        @endphp

                                                        @foreach ($bulanIndo as $bulan => $nama)
                                                            @php
                                                                $ada = $listPeriode->contains(function ($tanggal) use ($bulan) {
                                                                    return $tanggal && \Carbon\Carbon::parse($tanggal)->month === $bulan;
                                                                });
                                                            @endphp
                                                            {!! $nama . ' : ' . ($ada ? '<span style="color:green;">✅</span>' : '<span style="color:red;">❌</span>') !!}<br>
                                                        @endforeach
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
