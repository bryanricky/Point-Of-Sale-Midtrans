@extends('layouts.master')

@section('title')
    Dashboard
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Dashboard</li>
@endsection

@php
    $height = max(250, count($produk_terlaris) * 50); // minimal 250px
    $total_penjualan = $produk_terlaris->sum('total_terjual');
@endphp


@section('content')
<!-- Small boxes (Stat box) -->
<div class="row">
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-aqua">
            <div class="inner">
                <h3>{{ $kategori }}</h3>

                <p>Total Kategori</p>
            </div>
            <div class="icon">
                <i class="fa fa-cube"></i>
            </div>
            <a href="{{ route('kategori.index') }}" class="small-box-footer">Lihat <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-green">
            <div class="inner">
                <h3>{{ $produk }}</h3>

                <p>Total Produk</p>
            </div>
            <div class="icon">
                <i class="fa fa-cubes"></i>
            </div>
            <a href="{{ route('produk.index') }}" class="small-box-footer">Lihat <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-yellow">
            <div class="inner">
                <h3>{{ $member }}</h3>

                <p>Total Member</p>
            </div>
            <div class="icon">
                <i class="fa fa-id-card"></i>
            </div>
            <a href="{{ route('member.index') }}" class="small-box-footer">Lihat <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-red">
            <div class="inner">
                <h3>{{ $supplier }}</h3>

                <p>Total Supplier</p>
            </div>
            <div class="icon">
                <i class="fa fa-truck"></i>
            </div>
            <a href="{{ route('supplier.index') }}" class="small-box-footer">Lihat <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
    <!-- <div class="small-box bg-purple">
        <div class="inner">
            <h3>Rp{{ number_format($pendapatan_hari_ini, 0, ',', '.') }}</h3>
            <p>Pendapatan Hari Ini</p>
        </div>
        <div class="icon">
            <i class="fa fa-money"></i>
        </div>
        <a href="{{ route('laporan.index') }}" class="small-box-footer">Hari ini <i class="fa fa-calendar-check-o"></i></a>
    </div> -->
</div>

    <!-- ./col -->
     <div class="col-lg-3 col-xs-6">
    <!-- small box -->
    <!-- <div class="small-box bg-purple">
        <div class="inner">
            <h3>{{ $pelanggan }}</h3>

            <p>Total Pelanggan</p>
        </div>
        <div class="icon">
            <i class="fa fa-users"></i>
        </div>
        <a href="{{ route('pelanggan.index') }}" class="small-box-footer">Lihat <i class="fa fa-arrow-circle-right"></i></a>
    </div> -->
</div>

</div>
<form action="{{ route('dashboard') }}" method="GET" class="form-inline" style="margin-bottom: 20px;">
    <div class="form-group">
        <label for="tanggal_awal">Dari Tanggal:</label>
        <input type="date" name="tanggal_awal" id="tanggal_awal" class="form-control" value="{{ request('tanggal_awal') }}">
    </div>
    <div class="form-group" style="margin-left:10px;">
        <label for="tanggal_akhir">Sampai Tanggal:</label>
        <input type="date" name="tanggal_akhir" id="tanggal_akhir" class="form-control" value="{{ request('tanggal_akhir') }}">
    </div>
    <button type="submit" class="btn btn-primary" style="margin-left:10px;">Filter</button>
</form>
<!-- /.row -->
<!-- Main row -->
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Grafik Pendapatan {{ $tanggal_awal_format }} s/d {{ $tanggal_akhir_format }}</h3>

            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="chart">
                            <!-- Sales Chart Canvas -->
                            <canvas id="salesChart" style="height: 180px;"></canvas>
                        </div>
                        <!-- /.chart-responsive -->
                    </div>
                </div>
                <!-- /.row -->
            </div>
        </div>
        <!-- /.box -->
    </div>
    <!-- /.col -->
</div>
<!-- /.row (main row) -->
<div class="row">
    <!-- Grafik Produk -->
    <div class="col-md-6">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Detail Penjualan Produk {{ $tanggal_awal_format }} s/d {{ $tanggal_akhir_format }}</h3>
            </div>
            <div class="box-body">
                <canvas id="produkChart" style="height: {{ $height }}px;"></canvas>
            </div>
        </div>
    </div>
<!-- Produk Terlaris -->
<div class="col-md-6">
    <div class="box box-success">
        <div class="box-header with-border">
            <h3 class="box-title">Produk Terjual {{ $tanggal_awal_format }} s/d {{ $tanggal_akhir_format }}</h3>
        </div>
        <div class="box-body">
            @if (count($produk_terlaris))

                <ul class="list-group" style="max-height: 300px; overflow-y: auto;">
                    @foreach ($produk_terlaris as $produk)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ $produk->nama_produk }}
                            <span class="badge bg-blue">{{ $produk->total_terjual }} terjual</span>
                        </li>
                    @endforeach
                </ul>

                <!-- Highlight Total Penjualan -->
                <div class="text-center" style="margin-top: 20px;">
                    <div style="background: linear-gradient(45deg, #00c0ef, #0073b7); color: #fff; padding: 20px; border-radius: 10px;">
                        <h4 style="margin-bottom: 10px;">
                            <i class="fa fa-line-chart"></i> Total Penjualan Produk
                        </h4>
                        <span style="font-size: 28px; font-weight: bold;">{{ $total_penjualan }} produk</span>
                        <p style="margin: 0;">selama periode ini</p>
                    </div>
                </div>


            @else
                <p class="text-muted">Belum ada produk terjual bulan ini.</p>
            @endif
        </div>
    </div>
</div>

<!-- Daftar Pelanggan Berhutang -->
<div class="row">
    <div class="col-md-12">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">Pelanggan Berhutang</h3>
            </div>
            <div class="box-body">
                @if ($pelanggan_berhutang->count())
                    <div style="max-height: 280px; overflow-y: auto;"> <!-- Scrollable area -->
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Nama Pelanggan</th>
                                    <th class="text-right">Sisa Hutang (Rp)</th>
                                    <th class="text-center">Peringatan</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pelanggan_berhutang as $pelanggan)
                                    <tr>
                                        <td>{{ $pelanggan->nama_pelanggan }}</td>
                                        <td class="text-right">Rp{{ number_format($pelanggan->sisa_hutang, 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            @if (!empty($pelanggan->peringatan_tagihan))
                                                @if (substr($pelanggan->peringatan_tagihan, 0, 15) === 'Terlambat bayar')
                                                    <span class="text-danger">{{ $pelanggan->peringatan_tagihan }}</span>
                                                @else
                                                    <span class="text-primary">{{ $pelanggan->peringatan_tagihan }}</span>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $pesan = urlencode("Halo *{$pelanggan->nama_pelanggan}*, kami ingin mengingatkan bahwa Anda masih memiliki sisa hutang sebesar Rp" . number_format($pelanggan->sisa_hutang, 0, ',', '.') . ". Mohon segera melakukan pembayaran pada Toko Ridho. Terima kasih.");
                                                $nomorWa = preg_replace('/^0/', '62', $pelanggan->telepon);
                                            @endphp
                                            <a href="https://wa.me/{{ $nomorWa }}?text={{ $pesan }}" target="_blank" class="btn btn-success btn-sm">
                                                <i class="fa fa-whatsapp"></i> WA
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">Tidak ada pelanggan yang berhutang saat ini.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const produkData = @json($produk_terlaris_graph);

    const defaultProduk = Array.from({ length: 10 }, (_, i) => ({
        nama_produk: `Produk ${i + 1}`,
        total_terjual: 0
    }));

    produkData.forEach((item, index) => {
        if (index < 10) {
            defaultProduk[index] = item;
        }
    });

    const sortedData = defaultProduk.sort((a, b) => b.total_terjual - a.total_terjual);

    const labels = sortedData.map(item => item.nama_produk);
    const data = sortedData.map(item => item.total_terjual);

    // Tentukan nilai maksimum untuk sumbu Y agar tidak terlalu curam turunnya
    const maxValue = Math.max(...data);
    const suggestedMax = Math.ceil(maxValue * 1.2); // kasih jarak 20%

    const ctx = document.getElementById('produkChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Jumlah Terjual',
                data: data,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Grafik Penjualan Produk Top 10 (Terlaris)'
                },
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    suggestedMax: suggestedMax, // <= ini yang menstabilkan tinggi grafik
                    ticks: {
                        stepSize: Math.ceil(suggestedMax / 10)
                    }
                }
            }
        }
    });
</script>

<!-- ChartJS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('salesChart').getContext('2d');

        const salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($data_tanggal) !!},
                datasets: [{
                    // label dihapus agar tidak muncul di legend
                    data: {!! json_encode($data_pendapatan) !!},
                    fill: false,
                    borderColor: 'rgba(60, 141, 188, 1)',
                    backgroundColor: 'rgba(60, 141, 188, 0.5)',
                    tension: 0.3,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false // ini menyembunyikan legend
                    }
                }
            }
        });
    });
</script>
@endpush