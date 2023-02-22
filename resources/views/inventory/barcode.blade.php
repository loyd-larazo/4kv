<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">

      <script src="/js/jquery-3.6.1.min.js"></script>

      <title>Generate Barcode</title>
        
      <style type="text/css" media="print">
        @page {
          size: landscape;
          orientation: landscape;
        }

        @media print {
          body{
            /* transform:scale(3); */
          }
        }
      </style>

      <style>
        body {
          text-align: center;
        }

        svg {
          /* margin-top: calc(calc(100vh / 2) - 50px); */
          padding-right: 5px;
          padding-bottom: 5px;
        }
      </style>
  </head>
  <body onafterprint="window.close()">
    <?php
      for ($i = 1; $i <= $noPrint; $i++) {
        echo DNS1D::getBarcodeSVG($sku, 'C39', 1, 70);
      }
    ?>

    <script>
      $(function() {
        window.print();
      });
    </script>
  </body>
</html>
