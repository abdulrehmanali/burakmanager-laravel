<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>Production Product {{$production_product->name}}</title>
  <style>
  table {
    border-collapse: collapse;
    width: 100%;
  }

  tr {
    border-bottom: 1px solid #000000;
  }

  td {
    text-align: center;
  }
  </style>
</head>
<body>
<h2>{{$production_product->name}} ({{$production_product->sku}})</h2>

<table>
  <tr>
    <th>Product Name</th>
    <th>Quantity Required For 1 Product</th>
    <th>In Stock</th>
  </tr>
  @foreach ($production_product->products as $product)
  @php
      $inStock = 0;
      $measurementUnit = '';
      foreach($product->product->batches as $batches){
        $inStock += $batches->quantity;
        $measurementUnit = $batches->measurement_unit;
      }
  @endphp
  <tr>
    <td>{{ $product->product->name }}</td>
    <td>{{ $product->one_product_quantity }} {{ $measurementUnit }}</td>
    <td>{{ $inStock }} {{ $measurementUnit }}</td>
  </tr>
  @endforeach
</table>

</body>
</html>
