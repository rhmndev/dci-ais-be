<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Jalan - {{ $travelDocument->no }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10px; /* Set default font size to 10px */
        }

        .container {
            width: 100%; /* Use full width for landscape */
            margin: 0 auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 5px;
        }
        .w-full {
            width: 100%;
        }
        .w-half {
            width: 50%;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .logo {
            /* Style your logo if needed */
        }

        .company-info {
            /* Style your company information */
        }

        .surat-jalan-details {
            /* Style for Surat Jalan details */
        }

        

        .table-compact {
            padding: 0;
            margin: 0;
        }

        .table-compact tbody tr td {
            padding: 0;
            margin: 0;
        }

        .signatures {
            margin-top: 50px;
            text-align: center;
        }

        /* .signature {
            border-bottom: 1px solid #000;
            width: 200px;
            margin: 0 auto;
        } */
    </style>
</head>
<body>
  <table class="w-full" style="border: 0px">
    <tr style="border: 0px">
        <td class="w-half" style="border: 0px">
          <img src="{{ public_path('/img/logo.png') }}">
            <br>
            <small>Jl.Tekno Industri kawasan Industri Jababeka VIII No.1 Blok A3, Cikarang Kota, Kec. Cikarang Utara,
                Kabupaten Bekasi, Jawa Barat</small><br>
            <small>Ph: 62 231 123 123</small><br>
            <small>NPWP : 01.1234.1234.123</small>
        </td>
        <td class="w-half" style="border: 0px; text-align: right;">
          <table class="table-compact" style="border: 0px; float: right;">
              <tr style="border: 0px">
                  <td class="text-center bg-light" style="border: 0px; width: 50px;">
                      <h3><b>NO :</b></h3>
                  </td>
                  <td style="border: 0px">
                      {{$travelDocument->no}}
                  </td>
                  <td style="border: 0px; text-align: right;" rowspan="1">
                    {{-- <img src="{{ public_path('/img/sample_qr_sj.png') }}"alt="QR Code" style="width: 80px;"> --}}
                    @isset($travelDocument->qr_path)
                    <img src="{{ public_path('storage/'.$travelDocument->qr_path) }}" alt="QR Code" style="width: 80px;">
                    @endisset
                  </td>
              </tr>
              <tr style="border: 0px">
                  <td colspan="2" class="pl-2" style="border: 0px; padding-bottom: 10px;">
                      <h1><strong>Delivery Order</strong></h1>
                  </td>
              </tr> 
          </table>
        </td>
    </tr>
  </table>
  <hr>
    <div class="container">
        <div class="surat-jalan-details">
            <table style="border: 0px">
              <tr style="border: 0px">
                <td style="border: 0px">
                  <table style="border: 0px">
                    <tr style="border: 0px">
                      <td style="border: 0px; width: 70px;">Supplier Code:</td>
                      <td style="border: 0px">{{ $travelDocument->supplier_code }}</td> 
                    </tr>
                    <tr style="border: 0px">
                      <td style="border: 0px; width: 70px;">Supplier Name:</td>
                      <td style="border: 0px">{{ $travelDocument->supplier->name }}</td> 
                    </tr>
                    <tr style="border: 0px">
                      <td style="border: 0px; width: 70px;">Telp:</td>
                      <td style="border: 0px">{{ $travelDocument->supplier->phone }}</td> 
                    </tr>
                    <tr style="border: 0px">
                      <td style="border: 0px; width: 70px;">Address:</td>
                      <td style="border: 0px">{{ $travelDocument->supplier->address }}</td> 
                    </tr>
                  </table>
                </td>
                <td style="border: 0px">
                  <table>
                    <tr>
                        <td>Delivery Date:</td>
                        <td>{{ $travelDocument->order_delivery_date ? date('d-m-Y', strtotime($travelDocument->order_delivery_date)) : '-' }}</td> 
                    </tr>
                    <tr>
                        <td>PO Number:</td>
                        <td>{{ $travelDocument->po_number }}</td>
                    </tr>
                    <tr>
                        <td>PO Date:</td>
                        <td>{{ $travelDocument->purchaseOrder->order_date ? date('d-m-Y', strtotime($travelDocument->purchaseOrder->order_date)) : '-'}}</td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          
            <br>

            <table>
                <thead>
                    <tr>
                        <th style="width: 10px;">No</th>
                        <th style="width: 520px;">Item Description</th>
                        <th style="width: 20px;">Qty</th>
                        <th style="width: 20px;">Unit</th>
                        <th style="width: 120px;">Price</th>
                        <th style="width: 120px;">Total</th>
                        <th style="width: 10px;">Note</th>
                    </tr>
                </thead>
                <tbody>
                  @php $total = 0; $no = 1;@endphp 
                  @foreach ($groupedItems as $group)
                  <tr style="border-top: none; border-bottom: none;">
                    <td  style="text-align:center;">{{ $no++ }}</td>
                    <td>{{ $group['material']['code'] ?? 'code_item'}} - {{$group['material']['description'] ?? 'description_item'}}</td>
                    <td style="text-align:center;">{{ $group['total_qty'] }}</td>
                    <td style="text-align:center;">{{ $group['poItem']['unit_type'] }}</td>
                    <td style="text-align:right;">@currency($group['poItem']['unit_price'] ?? 0)</td> 
                    <td style="text-align:right;">@currency($group['poItem']['unit_price'] * $group['total_qty'])</td> 
                    <td>&nbsp;</td> 
                  </tr>
                  @php $total += $group['poItem']['unit_price'] * $group['total_qty']; @endphp
                  @endforeach
                  @for ($i = $no; $i <= 10; $i++) 
                  <tr style="border-top: none; @if ($i == 15) border-bottom: 1px solid #ddd; @else border-bottom: none; @endif">
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                  </tr>
                  @endfor
                </tbody>
                <tfoot>
                  <tr>
                    <td colspan="5" style="text-align:right;">total:</td>
                    <td style="text-align:right;">@currency($total ?? 0)</td> 
                    <td>&nbsp;</td> 
                  </tr>
                  <tr>
                    <td colspan="7"><strong>notes:</strong>{{ $travelDocument->notes }}</td>
                  </tr>
                </tfoot>
            </table>
        </div>
        <br>
        <table>
          <tr>
            <td style="border: none; padding: 0; text-align: center;">
              <div class="signature">
                <p>Made By:</p>
                <br>
                <br>
                <p>{{$travelDocument->made_by_user ?: '-'}}</p> 
              </div>
            </td>
            <td style="border: none; padding: 0; text-align: center;">
              <div class="signature">
                <p>Delivered By:</p>
                <br>
                <br>
                <p>{{$travelDocument->driver_name ?: '-'}}</p> 
              </div>
            </td>
            <td style="border: none; padding: 0; text-align: center;">
              <div class="signature">
                <p>Received By:</p>
                <br>
                <br>
                <p>(.......................................)</p> 
              </div>
            </td>
          </tr>
        </table>
        
    </div>

    <div style="position: fixed; bottom: 10px; left: 10px; font-size: 8px;">
      Page <span class="page-number">1</span> of <span class="total-pages">1</span> <br>
      Printed on: {{ date('d.m.Y H:i:s') }} 
    </div>
</body>
</html>
