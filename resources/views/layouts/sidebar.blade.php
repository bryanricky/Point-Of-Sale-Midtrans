<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="{{ asset(auth()->user()->foto ?? 'AdminLTE-2/dist/img/user2-160x160.jpg') }}" class="img-circle img-profil" alt="User Image">
            </div>
            <div class="pull-left info">
                <p>{{ auth()->user()->name }}</p>
                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>
        <!-- /.search form -->
        <!-- sidebar menu: : style can be found in sidebar.less -->
        <ul class="sidebar-menu" data-widget="tree">
            <li>
                <a href="{{ route('dashboard') }}">
                    <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                </a>
            </li>

            @if (auth()->user()->level == '1')
            <li class="header">MASTER</li>
            <li>
                <a href="{{ route('kategori.index') }}">
                    <i class="fa solid fa-cube"></i> <span>Kategori</span>
                </a>
            </li>
            <li>
                <a href="{{ route('produk.index') }}">
                    <i class="fa fa-cubes"></i> <span>Produk</span>
                </a>
            </li>
            <li>
                <a href="{{ route('member.index') }}">
                    <i class="fa fa-id-card"></i> <span>Member</span>
                </a>
            </li>
            <li>
                <a href="{{ route('supplier.index') }}">
                    <i class="fa fa-truck"></i> <span>Supplier</span>
                </a>
            </li>
            <li>
                <a href="{{ route('pelanggan.index') }}">
                    <i class="fa fa-user"></i> <span>Pelanggan</span>
                </a>
            </li>
            <li>
                <a href="{{ route('user.index') }}">
                    <i class="fa fa-users"></i> <span>User</span>
                </a>
            </li>
            <li class="header">TRANSAKSI</li>
            <!-- <li>
                <a href="{{ route('pengeluaran.index') }}">
                    <i class="fa fa-money"></i> <span>Pengeluaran</span>
                </a>
            </li> -->
            <li>
                <a href="{{ route('pembelian.index') }}">
                    <i class="fa fa-download"></i> <span>Pembelian</span>
                </a>
            </li>
            <li>
                <a href="{{ route('penjualankredit.index') }}">
                    <i class="fa fa-download"></i> <span>Penjualan Kredit</span>
                </a>
            </li>
            <!-- Tambahkan di bawah sini -->
            <li class="treeview">
                <a href="#">
                    <i class="fa fa-credit-card"></i> <span>Hutang Piutang</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    
                    <li><a href="{{ route('hutang.index') }}"><i class="fa fa-circle-o"></i> Daftar Hutang</a></li>
                    <li><a href="{{ route('hutang.pembayaran') }}"><i class="fa fa-circle-o"></i> Daftar Hutang Lunas</a></li>
                    <li><a href="{{ route('hutang_pelanggan.index') }}"><i class="fa fa-circle-o"></i> Daftar Piutang</a></li>
                    <li><a href="{{ route('hutang_pelanggan.pembayaran_lunas') }}"><i class="fa fa-circle-o"></i> Daftar Piutang Lunas</a></li>
                </ul>
            </li>
            <li>
                <a href="{{ route('penjualan.index') }}">
                    <i class="fa fa-upload"></i> <span>Penjualan</span>
                </a>
            </li>
            <li>
                <a href="{{ route('transaksi.index') }}">
                    <i class="fa fa-file-pdf-o"></i> <span>Transaksi Aktif</span>
                </a>
            </li>
            <li>
                <a href="{{ route('transaksi.baru') }}">
                    <i class="fa fa-file-pdf-o"></i> <span>Transaksi Baru</span>
                </a>
            </li>
            <li class="header">REPORT</li>
            <li>
                <a href="{{ route('laporan.index') }}">
                    <i class="fa fa-file-pdf-o"></i> <span>laporan</span>
                </a>
            </li>
            <li class="header">SYSTEM</li>
            
            <li>
                <a href="{{ route('setting.index') }}">
                    <i class="fa fa-cogs"></i> <span>Pengaturan</span>
                </a>
            </li>
            @else
            <!-- <li>
                <a href="{{ route('pelanggan.index') }}">
                    <i class="fa fa-user"></i> <span>Pelanggan</span>
                </a>
            </li>
            <li>
                <a href="{{ route('member.index') }}">
                    <i class="fa fa-id-card"></i> <span>Member</span>
                </a>
            </li> -->
            <li class="header">TRANSAKSI</li>
            <li>
                <a href="{{ route('pembelian.index') }}">
                    <i class="fa fa-download"></i> <span>Pembelian</span>
                </a>
            </li>
            <li>
                <a href="{{ route('penjualankredit.index') }}">
                    <i class="fa fa-download"></i> <span>Penjualan Kredit</span>
                </a>
            </li>
            <li class="treeview">
                <a href="#">
                    <i class="fa fa-credit-card"></i> <span>Hutang Piutang</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="{{ route('hutang.index') }}"><i class="fa fa-circle-o"></i> Hutang Supplier</a></li>
                    <li><a href="{{ route('hutang.pembayaran') }}"><i class="fa fa-circle-o"></i> Hutang Lunas Supplier</a></li>
                    <li><a href="{{ route('hutang_pelanggan.index') }}"><i class="fa fa-circle-o"></i> Piutang Pelanggan</a></li>
                    <li><a href="{{ route('hutang_pelanggan.pembayaran_lunas') }}"><i class="fa fa-circle-o"></i> Piutang Lunas Pelanggan</a></li>
                </ul>
            </li>
            <li>
                <a href="{{ route('transaksi.index') }}">
                    <i class="fa fa-file-pdf-o"></i> <span>Transaksi Aktif</span>
                </a>
            </li>
            <li>
                <a href="{{ route('transaksi.baru') }}">
                    <i class="fa fa-file-pdf-o"></i> <span>Transaksi Baru</span>
                </a>
            </li>
            <li class="header">REPORT</li>
            <li>
                <a href="{{ route('laporan.index') }}">
                    <i class="fa fa-file-pdf-o"></i> <span>laporan</span>
                </a>
            </li>
            @endif
        </ul>
    </section>
    <!-- /.sidebar -->
</aside>