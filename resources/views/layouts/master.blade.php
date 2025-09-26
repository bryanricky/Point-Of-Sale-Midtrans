<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ config ('app.name') }} | @yield('title')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" href="{{ asset('AdminLTE-2/bower_components/bootstrap/dist/css/bootstrap.min.css') }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('AdminLTE-2/bower_components/font-awesome/css/font-awesome.min.css') }}">
    <!-- Ionicons -->
    <link rel="stylesheet" href="{{ asset('AdminLTE-2/bower_components/Ionicons/css/ionicons.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('AdminLTE-2/dist/css/AdminLTE.min.css') }}">
    <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="{{ asset('AdminLTE-2/dist/css/skins/_all-skins.min.css') }}">
    <!-- Morris chart -->
    <link rel="stylesheet" href="{{ asset('AdminLTE-2/bower_components/morris.js/morris.css') }}">
    <!-- jvectormap -->
    <link rel="stylesheet" href="{{ asset('AdminLTE-2/bower_components/jvectormap/jquery-jvectormap.css') }}">
    <!-- Date Picker -->
    <link rel="stylesheet" href="{{ asset('AdminLTE-2/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="{{ asset('AdminLTE-2/bower_components/bootstrap-daterangepicker/daterangepicker.css') }}">
    <!-- bootstrap wysihtml5 - text editor -->
    <link rel="stylesheet" href="{{ asset('AdminLTE-2/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css') }}">
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('/AdminLTE-2/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic"> 

    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#000000">

    @stack('css')
</head>

<body class="hold-transition skin-purple-light sidebar-mini">
    <div class="wrapper">

        @includeIf(('layouts.header'))
        <!-- Left side column. contains the logo and sidebar -->

        @includeIf(('layouts.sidebar'))

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <h1>
                    @yield('title')
                </h1>
                <ol class="breadcrumb">
                    @section('breadcrumb')
                        <li><a href="{{ url ('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
                    @show                                  
                </ol>
            </section>

            <!-- Main content -->
            <section class="content">
                
                @yield('content')

            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->
        
        @includeIf('layouts.footer')

    </div>
    <!-- ./wrapper -->

    <!-- jQuery 3 -->
    <script src="{{ asset('AdminLTE-2/bower_components/jquery/dist/jquery.min.js') }}"></script>
    <!-- jQuery UI 1.11.4 -->
    <script src="{{ asset('AdminLTE-2/bower_components/jquery-ui/jquery-ui.min.js') }}"></script>
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script>
        $.widget.bridge('uibutton', $.ui.button);
    </script>
    <!-- Bootstrap 3.3.7 -->
    <script src="{{ asset('AdminLTE-2/bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <!-- Morris.js charts -->
    <script src="{{ asset('AdminLTE-2/bower_components/raphael/raphael.min.js') }}"></script>
    <script src="{{ asset('AdminLTE-2/bower_components/morris.js/morris.min.js') }}"></script>
    <!-- Sparkline -->
    <script src="{{ asset('AdminLTE-2/bower_components/jquery-sparkline/dist/jquery.sparkline.min.js') }}"></script>
    <!-- jvectormap -->
    <script src="{{ asset('AdminLTE-2/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js') }}"></script>
    <script src="{{ asset('AdminLTE-2/plugins/jvectormap/jquery-jvectormap-world-mill-en.js') }}"></script>
    <!-- jQuery Knob Chart -->
    <script src="{{ asset('AdminLTE-2/bower_components/jquery-knob/dist/jquery.knob.min.js') }}"></script>
    <!-- daterangepicker -->
    <script src="{{ asset('AdminLTE-2/bower_components/moment/min/moment.min.js') }}"></script>
    <script src="{{ asset('AdminLTE-2/bower_components/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
    <!-- datepicker -->
    <script src="{{ asset('AdminLTE-2/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
    <!-- Bootstrap WYSIHTML5 -->
    <script src="{{ asset('AdminLTE-2/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js') }}"></script>
    <!-- Slimscroll -->
    <script src="{{ asset('AdminLTE-2/bower_components/jquery-slimscroll/jquery.slimscroll.min.js') }}"></script>
    <!-- FastClick -->
    <script src="{{ asset('AdminLTE-2/bower_components/fastclick/lib/fastclick.js') }}"></script>
    <!-- ChartJS -->
    <script src="{{ asset('AdminLTE-2/bower_components/chart.js/Chart.js') }}"></script>
    <!-- DataTables -->
    <script src="{{ asset('AdminLTE-2/bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('AdminLTE-2/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset('AdminLTE-2/dist/js/adminlte.min.js') }}"></script>
    <!-- AdminLTE dashboard demo (This is only for demo purposes) -->
    <!-- <script src="{{ asset('AdminLTE-2/dist/js/pages/dashboard.js') }}"></script> -->
    <!-- AdminLTE dashboard demo (This is only for demo purposes) -->
    <!-- <script src="{{ asset('AdminLTE-2/dist/js/pages/dashboard2.js') }}"></script> -->
    <!-- Validator -->
    <script src="{{ asset('js/validator.min.js') }}"></script>


    <!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Pusher -->
<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script>
Pusher.logToConsole = true;

var pusher = new Pusher("{{ env('PUSHER_APP_KEY') }}", {
  cluster: "{{ env('PUSHER_APP_CLUSTER') }}",
  encrypted: true
});

var channel = pusher.subscribe('stok-channel');

channel.bind('stok-menipis', function(data) {
  if (Array.isArray(data)) {
    let list = '<ul style="text-align: left; margin:0; padding-left: 20px;">';
    data.forEach(item => {
      list += `<li>${item.nama_produk} (${item.stok})</li>`;
    });
    list += '</ul>';

    Swal.fire({
  toast: true,
  position: 'top-end',
  icon: 'warning',
  title: 'Stok Menipis!',
  html: `Ada ${data.length} produk:<br>
         <div id="stok-menipis-list" style="text-align: left; margin:0; padding-left: 20px; cursor:pointer;">
           ${list}
         </div>`,
  showConfirmButton: true,
  confirmButtonText: 'Tutup',
  timer: null,
  didOpen: () => {
    const listDiv = document.getElementById('stok-menipis-list');
    listDiv.addEventListener('click', () => {
      window.location.href = "{{ route('produk.index') }}";
    });
  }
});

  } else {
    Swal.fire({
  toast: true,
  position: 'top-end',
  icon: 'warning',
  title: 'Stok Menipis!',
  html: `Ada ${data.length} produk:<br>
         <div id="stok-menipis-list" style="text-align: left; margin:0; padding-left: 20px; cursor:pointer;">
           ${list}
         </div>`,
  showConfirmButton: true,
  confirmButtonText: 'Tutup',
  timer: null,
  didOpen: () => {
    const listDiv = document.getElementById('stok-menipis-list');
    listDiv.addEventListener('click', () => {
      window.location.href = "{{ route('produk.index') }}";
    });
  }
});

  }

  if (typeof table !== 'undefined') {
    table.ajax.reload();
  }
});


// Polling
setInterval(cekStokMenipis, 5000);

function cekStokMenipis() {
  $.get('/produk/cek-stok-menipis', function(data) {
    if (data.length > 0) {
      let stokHabis = data.filter(item => item.stok === 0); // Produk dengan stok 0
      let stokMenipis = data.filter(item => item.stok > 0 && item.stok <= 5); // Produk dengan stok 1-5

      let judul = '';
      if (stokHabis.length > 0) {
        judul = 'Stok Habis!';
      } else if (stokMenipis.length > 0) {
        judul = 'Stok Menipis!';
      }

      let list = '<ul style="text-align: left; margin:0; padding-left: 20px;">';
      [...stokHabis, ...stokMenipis].forEach(item => {
        list += `<li>${item.nama_produk} (${item.stok})</li>`;
      });
      list += '</ul>';

      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'warning',
        title: judul,
        html: `Ada ${data.length} produk:<br>
               <div id="stok-menipis-list" style="text-align: left; margin:0; padding-left: 20px; cursor:pointer;">
                 ${list}
               </div>`,
        showConfirmButton: true,
        confirmButtonText: 'Tutup',
        timer: null,
        didOpen: () => {
          const listDiv = document.getElementById('stok-menipis-list');
          listDiv.addEventListener('click', () => {
            window.location.href = "{{ route('produk.index') }}";
          });
        }
      });
    }
  });
}
</script>
    @stack('scripts')
    <!-- PWA Service Worker -->
    <script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/serviceworker.js')
        .then(function(reg) {
            console.log('✅ Service Worker Registered! Scope:', reg.scope);
        })
        .catch(function(error) {
            console.error('❌ Service Worker registration failed:', error);
        });
    }
    </script>
</body>
</html>