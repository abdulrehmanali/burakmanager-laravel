<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Invoice #{{ $entry->id }} ({{ date('d/m/Y H:m:s') }})</title>
</head>

<body>
	<style>
		* {
			font-family: sans-serif;
		}

		.page-break {
			page-break-after: always;
		}

		table,
		tr {
			width: 100%;
			padding: 0;
		}

		td {
			width: 50%;
		}

		.products_table_head {
			background-color: #000000;
			color: #ffffff;
		}

		table.products_table,
		.products_table td {
			border-left: 1px solid black;
			border-right: 1px solid black;
			border-bottom: 1px solid black;
			border-collapse: collapse;
			text-align: left;
		}

		.products_table th {
			border-left: 1px solid #ffffff;
			border-right: 1px solid #ffffff;
			font-size: 18px;
			text-align: left;
			padding: 8px;
		}

		.products_table th:nth-child(1) {
			border-left: 1px solid black;
		}

		.products_table td {
			font-size: 16px;
			padding: 8px;
		}

		.mb-0 {
			margin-bottom: 0px;
		}

		.mb-1 {
			margin-bottom: 0.25rem;
		}

		.mb-2 {
			margin-bottom: 0.5rem;
		}

		.mb-3 {
			margin-bottom: 1rem;
		}

		.mb-4 {
			margin-bottom: 1.5rem;
		}

		.mb-5 {
			margin-bottom: 3rem;
		}
	</style>
	<table>
		<tr>
			<td>
				<h3 class="mb-0">{{ $shop->name }}</h3>
				<p class="mb-0">{{ $shop->address }}</p>
			</td>
			<td style="text-align: right;">
				<h1>INVOICE</h1>
				<h2 style="text-align: right;"># {{ $entry->id }}</h2>
			</td>
		</tr>
		<tr>
			<td>
				<h3 class="mb-0">Bill To: </h3>
				<p class="mb-0">{{ $entry->customer->company_name }}</p>
				<p>{{ $entry->customer->address }}</p>
			</td>
			<td style="text-align: right;">
				<p>
					<b>Created At:</b>
					{{ date('d/m/Y', strtotime($entry->created_at)) }}
				</p>
				<p class="mb-0">
					<b>Printed At:</b>
					{{ date('d/m/Y') }}
				</p>
			</td>
		</tr>
	</table>
	<table class="products_table">
		<tr class="products_table_head">
			<th>Item Name</th>
			<th>Quantity</th>
			<th>Price</th>
			<th>Total</th>
		</tr>
		@php
			$total = 0;
		@endphp
		@foreach ($products as $product)
			@php
				$name = isset($product->product->name) ? $product->product->name : $product->product_name;
			@endphp
			<tr>
				<td>{{ $name }}</td>
				<td>{{ $product->quantity }}</td>
				<td>Rs {{ $product->rate }}</td>
				<td>Rs {{ $product->rate * $product->quantity }}</td>
			</tr>
			@php
				$total += $product->rate * $product->quantity;
			@endphp
		@endforeach
	</table>
	<table>
		<tr>
			<td></td>
			<td style="text-align: right;">
				<p class="mb-0"><b>Total:</b> Rs {{ $total }}</p>
			</td>
		</tr>
		<tr>
			<td></td>
			<td style="text-align: right;">
				<p class="mb-0"><b>Amount Recived:</b> Rs {{ $entry->amount_received() }}</p>
			</td>
		</tr>
		<tr>
			<td></td>
			<td style="text-align: right;">
				<p><b>Pending Payment:</b> Rs {{ $total - $entry->amount_received() }}</p>
			</td>
		</tr>
	</table>
</body>

</html>
