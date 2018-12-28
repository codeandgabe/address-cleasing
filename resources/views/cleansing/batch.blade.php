@extends('layouts.master')
@section('title', 'Batch Jobs')

<table class="table table-striped">
	<tr>
		<td>key</td>
		<td>status</td>
		<td class="text-center">download</td>
		<td class="text-center">limit offset</td>
	</tr>
@foreach ($jobs as $job)
	<tr>
		<td>{{$job->key}}</td>
		<td>{{$job->status}}</td>
		<td class="text-center">
			<a href="https://batch.geocoder.api.here.com/6.2/jobs/{{$job->key}}/result?app_id=aqyRtDyoNJMewbgpngW8&app_code=64KKq04PuzCnZTpZns-GdQ"><i class="fas fa-download"></i></a>
		</td>
		<td>{{$job->limitoffset}}</td>
	</tr>
@endforeach
</table>

{!! $jobs->render() !!}
