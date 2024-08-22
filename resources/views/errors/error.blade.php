@extends('layouts.error')
@section('title', 'Error!')
@section('content')
    <div class="row"><br/><br/><br/>
    	<div class="col-md-6 col-md-offset-3">
			@if($errorMessage!='')
			        <div style="color:red; font-size: 18px;">
			                {{ $errorMessage }}
			        </div>	
			@endif	

			<p align="center"><a href="{{ ($backUrl!='')? $backUrl: route('dashboard') }}" style="font-size: 18px;"><u>< Back</u></a> </p>			   		
    	</div>
  	</div>
@endsection