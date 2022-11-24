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
          border-top: 1px solid black;
          border-collapse: collapse;
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
          Tuguegarao, <br>
          Cagayan Valley
        </p>
        <hr>
        <p>
          <?php
            $strDate = strtotime($sale->created_at);
            $transDate = getDate($strDate);
          ?>
          Date: {{ $transDate['month']." ".$transDate['mday'].", ".$transDate['year'] }}
        </p>
        <table>
          <tbody>
            @foreach($sale->items as $item)
              <tr>
                <td class="name">{{ $item->item->name }}</td>
                <td class="price">
                  {{ $item->quantity }} x P{{ number_format($item->amount) }}<br>
                  P{{ number_format($item->total_amount) }}
                </td>
              </tr>
            @endforeach
            <tr>
              <td class="name">TOTAL</td>
              <td class="price">P{{ number_format($sale->total_amount) }}</td>
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
