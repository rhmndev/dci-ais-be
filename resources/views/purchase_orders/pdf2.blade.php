<!DOCTYPE html>
<html>
<head>
<style>
table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
  font-size: 12px; /* Adjust font size as needed */
}

td, th {
  border: 1px solid #dddddd;
  text-align: left;
  padding: 8px;
  font-size: 12px; /* Adjust font size as needed */
}

tr:nth-child(even) {
  background-color: #dddddd;
}

body {
  font-size: 12px; /* Adjust font size for the entire document */
}

h3, p { /* Adjust font size for headings and paragraphs */
  font-size: 14px; 
}
</style>
</head>
<body>
  <img src="{{ public_path('/img/logo.png') }}">
<hr>

<h3>Purchase Order</h3>
<p>PO: {{ $purchaseOrder->po_number }}</p>
<p>1601 - DCI Cikarang</p>
<p>04-Oktober-2024</p>

<table>
  <tr>
    <th>Kepada</th>
    <th>Delivery Address</th>
  </tr>
  <tr>
    <td>Taylor Dickens<br>70 Bowman St.<br>South Windsor, CT 06074<br>USA<br>Terms: 30 Days<br>Phone No: 800-123-4567<br>Attn: John Sullivan<br>Email: john@taylordickens.com</td>
    <td>Boston Office<br>One Post Office Square, Suite 3600<br>Boston MA, 02109<br>USA<br>Phone No: 800-504-3364<br>Attn: Patrick</td>
  </tr>
</table>

<hr>

<table>
  <tr>
    <th>Delivery Date</th>
    <th>Requested By</th>
    <th>Approved By</th>
    <th>Department</th>
  </tr>
  <tr>
    <td>04/28/2017</td>
    <td>Patrick Smith</td>
    <td>Patrick Smith</td>
    <td>IT Department</td>
  </tr>
</table>

<hr>

<h3>Notes</h3>
<p>Description ABC</p>

<hr>

<table>
  <tr>
    <th></th>
    <th>Item Name</th>
    <th>Item Code</th>
    <th>Qty.</th>
    <th>Item Price</th>
    <th>Disc.</th>
    <th>Total</th>
  </tr>
  <tr>
    <td></td>
    <td>Nescafe Gold Blend Coffee 7oz.</td>
    <td>QD2-0035 </td>
    <td>2</td>
    <td>$ 5.00</td>
    <td>$ 0.00</td>
    <td>$ 10.00</td>
  </tr>
  <tr>
    <td>Tea Earl Grey 7oz.</td>
    <td>QD2-0036</td>
    <td>2</td>
    <td>$ 5.00</td>
    <td>$ 0.00</td>
    <td>$ 10.00</td>
  </tr>
  <tr>
    <td>Tea English Breakfast 7oz.</td>
    <td>QD2-0037</td>
    <td>2</td>
    <td>$ 5.00</td>
    <td>$ 0.00</td>
    <td>$ 10.00</td>
  </tr>
  <tr>
    <td colspan="5" style="text-align:right;">Subtotal:</td>
    <td>$ 30.00</td>
  </tr>
  <tr>
    <td colspan="5" style="text-align:right;">Tax (8%):</td>
    <td>$ 2.40</td>
  </tr>
  <tr>
    <td colspan="5" style="text-align:right;">Total:</td>
    <td>$ 32.40</td>
  </tr>
</table>

</body>
</html>