@extends('layouts.master')
@section('title', 'Upload de Planilha')
{{-- 
<form action="upload" method="POST" class="form" enctype="multipart/form-data">
	<input type="file" name="planilha" class="form-control">
	<input type="submit" value="enviar"  class="btn btn-danger">
</form>
 --}}


<h2>upload de resultado do batch job</h2>
<form action="upload/batchjob" method="POST" class="form-inline" role="form" enctype="multipart/form-data">
	<div class="form-group">
		<label class="sr-only" for="">label</label>
	<input type="file" name="txt" class="form-control">
	</div>
	<button type="submit" class="btn btn-primary">Enviar</button>
</form>


<h2>upload de planilha</h2>
<form action="upload" method="POST" class="form-inline" role="form" enctype="multipart/form-data">
	<div class="form-group">
		<label class="sr-only" for="">label</label>
	<input type="file" name="planilha" class="form-control">
	</div>
	<button type="submit" class="btn btn-primary">Enviar</button>
</form>



