<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>Recipt For Order #{{$entry->id}} ({{date('d/m/Y H:m:s')}})</title>
</head>
<body>
  <style>
    .page-break {
        page-break-after: always;
    }
    table, tr {
      width:100%;
      padding: 0;
    }
    td{
      width:50%;
    }
    </style>
    @if (!request('html'))
    <style>
      h1 {
        font-size: 32px;
      }
      h2 {
        font-size: 28px;
      }
      p {
        font-size: 24px;
      }
    </style>
    @else
    <style>
      * {
        padding: 0;
      }
      h1 {
        font-size: 16px;
      }
      h2, h3 {
        font-size: 14px;
      }
      p {
        font-size: 12px;
      }
      td{
        width:50%;
      }
      .products_table td {
        width: 33%;
      }
    </style>
    @endif
    <h1 style="text-align:center;">{{$shop->name}}</h1>
    <h2 style="text-align:center;">{{$shop->address}}</h2>
    <h3 style="text-align:center;">Order #{{$entry->id}}</h3>
    <table>
      <tr>
        <td><p>Type: </p></td>
        <td><p style="text-align: right;width:100%">{{$entry->type}}</p></td>
      </tr>
      <tr>
        <td><p>Customer: </p></td>
        <td><p style="text-align: right;width:100%">{{$entry->customer->name}}</p></td>
      </tr>
      <tr>
        <td><p>Printed At :</p></td>
        <td><p style="text-align: right;width:100%">{{date('h:i / d-m-Y')}}</p></td>
      </tr>
    </table>
    <hr>
    <h2>Products:</h2>
    <table class="products_table">
      @php
          $total = 0;
      @endphp
      <tr>
        <td><p>Name</td>
        <td><p style="text-align: center;width:100%">Qty</p></td>
        <td><p style="text-align: right;width:100%">Price</p></td>
      </tr>
      @foreach ($products as $product)
      @php
          $name = (isset($product->product->name) ? $product->product->name : $product->product_name)
      @endphp
        <tr>
          <td><p>{{$name}}</p></td>
          <td><p style="text-align: center;width:100%">{{$product->quantity}}</p></td>
          <td><p style="text-align: right;width:100%"> Rs {{$product->rate * $product->quantity}}</p></td>
        </tr>
        @php
            $total += $product->rate * $product->quantity;
        @endphp
      @endforeach
    </table>
    <hr>
    <table>
      <tr>
        <td><p>Total: </p></td>
        <td><p style="text-align: right;width:100%">Rs {{$total}}</p></td>
      </tr>
      <tr>
        <td><p>Number Of Items: </p></td>
        <td><p style="text-align: right;width:100%">{{count($products)}}</p></td>
      </tr>
      <tr>
        <td><p>Amount Recived: </p></td>
        <td><p style="text-align: right;width:100%">Rs {{$entry->amount_received}}</p></td>
      </tr>
      <tr>
        <td><p>Pending Payment: </p></td>
        <td><p style="text-align: right;width:100%">Rs {{$total - $entry->amount_received}}</p></td>
      </tr>
      <tr>
        <td><p>Pending Status: </p></td>
        <td><p style="text-align: right;width:100%">{{$entry->payment_status}}</p></td>
      </tr>
      <tr>
        <td><p>Payment Method: </p></td>
        <td><p style="text-align: right;width:100%">{{$entry->payment_method}}</p></td>
      </tr>
    </table>
    <p style="text-align: center;width:100%">Powerd By Burakmanager.web.app</p>
</body>
</html>