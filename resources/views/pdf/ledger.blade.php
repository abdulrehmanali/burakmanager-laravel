<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>Ledger</title>
  <style>
    table {
      border-collapse: collapse;
      width: 100%;
    }
    td,th {
      border: 1px solid #000000;
    }
    </style>
</head>
@php
$total = 0;
$pending = 0; 
@endphp
<body>
  <table>
    <tr>
      <th>ID</th>
      <th>Date</th>
      <th>Customer Name</th>
      <th>Type</th>
      <th>Payment Method</th>
      <th>Total</th>
      <th>Pending Payment</th>
    </tr>
    @foreach ($entries as $entry)
    @php
      if ($entry->total > $entry->amount_received()) {
        $pending += $entry->total - $entry->amount_received();
      }
      $total += $entry->amount_received();
    @endphp
    <tr>
      <td>{{$entry->id}}</td>
      <td>{{date('Y-m-d', strtotime($entry->created_at))}}</td>
      <td>{{$entry->customer_name}}</td>
      <td>{{$entry->type}}</td>
      <td>{{$entry->payment_method}}</td>
      <td>{{number_format($entry->total, 2)}}</td>
      <td>{{ ($entry->total > $entry->amount_received() ? number_format($entry->total - $entry->amount_received(), 2) : 0.00)}}</td>
    </tr>
    @endforeach
    <tr>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td>Total: </td>
      <td>{{ number_format($total, 2)}}</td>
    </tr>
    <tr>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td>Pending Amount: </td>
      <td>{{ (number_format($pending, 2))}}</td>
    </tr>
  </table>
</body>
</html>