@extends('layouts.master')
@section('title', 'Upload de Planilha')

<table class="table">
	<tr>
		<td>nome</td>
		<td>relevância</td>
		<td>STATUS</td>
	</tr>
@foreach ($enderecos as $end)
	<tr>
		<td>{{$end->CLEANED_ADDRESS}}</td>
		<td>{{$end->C_REL}}</td>
		<td>{{$end->C_STATUS}}</td>
	</tr>
@endforeach
</table>

{!! $enderecos->render() !!}
