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

<!-- Selamat Datang Kasir -->
<div class="row">
    <div class="col-lg-12 text-center mb-4">
        <div class="jumbotron jumbotron-fluid bg-info text-white">
            <h1 class="display-4">Selamat Datang di Kasir!</h1>
            <p class="lead">Anda login sebagai <strong>KASIR</strong></p>
        </div>
    </div>
</div>

@if(session('kas_awal'))
    <div class="alert alert-info">
        Modal awal kasir: <strong>Rp {{ number_format(session('kas_awal'), 0, ',', '.') }}</strong>
    </div>
@endif

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
                    <div style="background: linear-gradient(45deg, #00c0ef, #0073b7); color: #fff; padding: 20px; border-radius: 10px; min-width: 250px;">

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