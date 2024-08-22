@extends('reseller.admin.layouts.exception')
@section('title', 'Error!')
@section('content')
<div class="content-wrapper">
    <!-- Content Header Section End -->
    <section class="content" style="color:red; font-size: 14px;">
        <!-- Message Start -->
        <div>Error: {{ $exception->getMessage() }}</div>
        <div style="margin-top: 20px;"> <p align="center">
        	<!-- <a class="btn btn-primary" href="{{ (url()->previous()!='')? url()->previous(): route('dashboard') }}">Back</a> -->
            <a class="btn btn-primary" href="{{ route('dashboard') }}">Back to Dashboard</a>
        </p>
        </div>
        <!-- Message End -->
    </section>

</div>
@endsection
