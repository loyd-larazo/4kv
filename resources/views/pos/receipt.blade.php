<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">

      <script src="/js/jquery-3.6.1.min.js"></script>

      <title>Generate Barcode</title>
        
      <style type="text/css" media="print">
        @page {
          margin: 0in; 
        }
      </style>

      <style>
        
        * {
          font-size: 12px;
          font-family: 'Times New Roman';
        }

        td, th, tr, table {
          border-collapse: collapse;
        }

        td {
          border-top: 1px solid black;
        }

        .no-border, .no-border td {
          border-top: 0px !important;
        }

        .centered {
          text-align: center;
          align-content: center;
        }

        .ticket {
          width: 155px;
          max-width: 155px;
        }

        img {
          max-width: 100%;
          width: inherit;
        }

        svg {
          width: 120px;
          height: 40px;
        }

        table {
          width: 100%;
          margin-top: 5px;
        }

        .name {
          width: 90px;
        }

        .price {
          text-align: right;
        }
      </style>
  </head>
  @if($sale)
    <body class="ticket" onafterprint="window.close()">
      <div >
        <img src="/images/logo-bnw.png" alt="Logo">
        <p class="centered">
          Provincial Road, Cataggaman Nuevo, Tuguegarao City<br>
          No. 09452468528
        </p>
        <p>
          Cashier: {{ $sale->user->firstname }} <br>
          Date: {{ $createdDate }}
        </p>
        <table>
          <tbody>
            @foreach($sale->items as $item)
              <tr>
                <td class="name">
                  {{ $item->item->name }}<br>
                  {{ $item->quantity }} x P{{ number_format($item->amount, 2, '.', ',') }}
                </td>
                <td class="price" style="vertical-align: top">
                  P{{ number_format($item->total_amount, 2, '.', ',') }}
                </td>
              </tr>
            @endforeach
            <tr>
              <td class="name">TAX: 12%</td>
              <td class="price">P{{ $vat }}</td>
            </tr>
            <tr class="no-border">
              <td class="name">DISCOUNT</td>
              <td class="price">P{{ number_format($sale->total_discount, 2, '.', ',') }}</td>
            </tr>
            <tr class="no-border">
              <td class="name">TOTAL</td>
              <td class="price">P{{ number_format($sale->total_amount, 2, '.', ',') }}</td>
            </tr>
            <tr class="no-border">
              <td class="name">PAID AMOUNT</td>
              <td class="price">P{{ number_format($sale->paid_amount, 2, '.', ',') }}</td>
            </tr>
            <tr class="no-border">
              <td class="name">CHANGE</td>
              <td class="price">P{{ number_format($sale->change_amount, 2, '.', ',') }}</td>
            </tr>
          </tbody>
        </table>
        <p class="centered">
          Thanks for visiting our store!<br>
          Please comeback again soon.
        </p>

        <div class="centered">
          <?php echo DNS1D::getBarcodeSVG($sale->reference, 'C39', 1, 50) ?>
          {{ $sale->reference }}
        </div>
      </div>

      <script>
        $(function() {
          window.print();
        });
      </script>
    </body>
  @endif
</html>
