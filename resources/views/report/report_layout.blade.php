<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>4KV Hardware and Construction Supply</title>
      
    <link href="/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="/fontawesome/css/all.min.css" rel="stylesheet">
    <link href="/css/app.css" rel="stylesheet">

    <script src="/js/jquery-3.6.1.min.js"></script>
    <script src="/bootstrap/js/bootstrap.min.js"></script>
    <style type="text/css" media="print">
      @media print {
        @page {
          margin-bottom: 0px;
          
          .table-rpt thead {
            background: #e9ecef;
          }
        }
      }
    </style>
    <script>
      $(function() {
        var landscape = 1;
        if (landscape) {
          var css = '@page { size: landscape; }';
          var head = document.head || document.getElementsByTagName('head')[0];
          var style = document.createElement('style');

          style.type = 'text/css';
          style.media = 'print';

          if (style.styleSheet){
            style.styleSheet.cssText = css;
          } else {
            style.appendChild(document.createTextNode(css));
          }
          head.appendChild(style);
        }
        
        window.print();
      });
    </script>
  </head>
  <body onafterprint="window.close()">
    <div id="layoutSidenav">
      <main class="w-100 mt-1">
        <div class="container-fluid">
          
          @yield('content')
          
        </div>
      </main>
    </div>
  </body>
</html>