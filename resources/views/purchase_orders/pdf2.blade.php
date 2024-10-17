<!DOCTYPE html>
<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

  <style>
        table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            width: 100%;
            font-size: 10px;
            /* Reduced font size */
        }

        .w-full {
            width: 100%;
        }
        .w-half {
            width: 50%;
        }

        td,
        th {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 6px;
            /* Reduced padding */
            font-size: 10px;
            /* Reduced font size */
        }

        /* tr:nth-child(even) {
            background-color: #dddddd;
        } */

        body {
            font-size: 10px;
            /* Reduced font size for the entire document */
        }

        h3,
        p {
            /* Reduced font size for headings and paragraphs */
            font-size: 12px;
        }

        .table-compact {
            padding: 0;
            margin: 0;
        }

        .table-compact tbody tr td {
            padding: 0;
            margin: 0;
        }
    </style>
</head>

<body>
  <table class="w-full" style="border: 0">
    <tr style="border: 0">
        <td class="w-half" style="border: 0">
          <img src="{{ public_path('/img/logo.png') }}">
            <br>
            <small>Jl.Tekno Industri kawasan Industri Jababeka VIII No.1 Blok A3, Cikarang Kota, Kec. Cikarang Utara,
                Kabupaten Bekasi, Jawa Barat</small><br>
            <small>Ph: 62 231 123 123</small><br>
            <small>NPWP : 01.1234.1234.123</small>
        </td>
        <td class="w-half" style="border: 0">
          <table class="table-compact">
              <tr>
                  <td class="text-center bg-light">
                      <h3><b>PO</b></h3>
                  </td>
                  <td class="p-2">
                      {{ $purchaseOrder->po_number }}<br>
                      1601 - DCI Cikarang<br>
                      04-Oktober-2024
                  </td>
              </tr>
              <tr>
                  <td colspan="2" class="pl-2">
                      <strong>Purchase Order</strong>
                  </td>
              </tr>
          </table>
        </td>
    </tr>
  </table>

  <table class="table table-borderless table-compact">
    <tr style="border: 0">
      <td style="border: 0">
        <table class="table table-borderless table-compact">
          <tr>
              <td>Kepada</td>
              <td>:</td>
              <td>{{ $purchaseOrder->supplier->name }}</td>
          </tr>
          <tr>
              <td>Alamat</td>
              <td>:</td>
              <td>{{ $purchaseOrder->supplier->address }}</td>
          </tr>
          <tr>
              <td>Kota</td>
              <td>:</td>
              <td>{{ $purchaseOrder->supplier->city }}</td>
          </tr>
          <tr>
              <td>Telepon</td>
              <td>:</td>
              <td>{{ $purchaseOrder->supplier->phone }}</td>
          </tr>
          <tr>
              <td>Fax</td>
              <td>:</td>
              <td>{{ $purchaseOrder->supplier->fax }}</td>
          </tr>
          <tr>
              <td>Email</td>
              <td>:</td>
              <td>{{ $purchaseOrder->supplier->email }}</td>
          </tr>
        </table>
      </td>
      <td style="border: 0">
        <table class="table table-borderless table-compact">
          <tr>
              <td>Mata Uang</td>
              <td>: {{ $purchaseOrder->purchase_currency_type }}</td>
          </tr>
          <tr>
              <td>Tgl. Kirim</td>
              <td>: By Schedule</td>
          </tr>
          <tr>
              <td>Dikirim ke Gudang</td>
              <td>: MT01 - Maintenance</td>
          </tr>
          <tr>
              <td>User</td>
              <td>: {{ $purchaseOrder->user }}</td>
          </tr>
      </table>
      </td>
    </tr>
  </table>

    <table class="table-compact">
        <thead>
          <tr>
            <th class="text-center">No</th>
            <th>Material ID / Description</th>
            <th class="text-center">Qty</th>
            <th class="text-center">Unit</th>
            <th class="text-center">Price</th>
            <th class="text-center">Amount</th>
        </tr>
        </thead>
        <tbody>
          @php $no = 1; @endphp
          @foreach($purchaseOrder->items as $item)
          <tr>
              <td class="text-center">{{$no++}}</td>
              <td>{{ isset($item->material) ?? $item->material->code }} / {{ isset($item->material) ?? $item->material->description }}</td>
              <td class="text-center">{{ $item->quantity }}</td>
              <td class="text-center">{{ $item->unit_type }}</td>
              <td class="text-center">{{ $item->price }}</td> 
              <td>{{ $item->unit_price }}</td> 
          </tr>
          @endforeach
          <tr>
              <td rowspan="10" colspan="2">
                <strong>Catatan: </strong>
                  <ol>
                      <li>Pastikan barang yang dikirim sesuai dengan spesifikasi.</li>
                      <li>Sertakan dokumen pendukung seperti invoice dan packing list.</li>
                      <li>Konfirmasi penerimaan barang akan dilakukan setelah pemeriksaan.</li>
                      <li>Pembayaran akan dilakukan sesuai dengan termin yang disepakati.</li>
                  </ol>
              </td>
          </tr>
        </tbody>
        <tfoot>
          <tr>
              <td colspan="2" style="text-align:right;">Subtotal:</td>
              <td>30.00</td>
          </tr>
          <tr>
              <td colspan="2" style="text-align:right;">PPN:</td>
              <td>{{$purchaseOrder->tax}}</td>
          </tr>
          <tr>
              <td colspan="2" style="text-align:right;">Total:</td>
              <td>{{$purchaseOrder->total_amount}}</td>
          </tr>
          <tr>
              <td class="text-center"><strong>Dicek Oleh</strong></td>
              <td class="text-center"><strong>Diketahui Oleh</strong></td>
              <td class="text-center"><strong>Disetujui Oleh</strong></td>
          </tr>
          <tr>
              <td class="text-center">
                @if($purchaseOrder->is_checked)
                  <h4>SIGNED</h4>
                  <small>{{$purchaseOrder->checked_at}}</small><br>
                  <small>{{$purchaseOrder->user_checked->full_name}}</small><br>
                  <small>{{$purchaseOrder->user_checked->department}}</small>
                @endif
              </td>
              <td class="text-center">
                @if($purchaseOrder->is_knowed)
                  <h4>SIGNED</h4>
                  <small>{{$purchaseOrder->knowed_at}}</small><br>
                  <small>{{$purchaseOrder->user_knowed->full_name}}</small><br>
                  <small>{{$purchaseOrder->user_knowed->department}}</small>
                @else

                @endif
              </td>
              <td class="text-center">
                @if($purchaseOrder->is_approved)
                <h4>SIGNED</h4>
                <small>{{$purchaseOrder->approved_at}}</small><br>
                <small>{{$purchaseOrder->user_approved->full_name}}</small><br>
                <small>{{$purchaseOrder->user_approved->department}}</small>
                @endif
              </td>
          </tr>
        </tfoot>
    </table>

    <small>Dicetak: </small>

</body>

</html>