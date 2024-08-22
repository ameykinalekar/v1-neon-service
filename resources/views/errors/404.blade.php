<?php header('Location:/',true,301); exit();?>
@extends('front.layouts.error' , ['title' =>  '404'])
@section('content')

<div class="bnr-part">
    <div class="wrapper">
        <div class="row">
            <div class="col-md-12">
                <div class="page-head">
                    <h2>404
                </div>
            </div>
        </div>
    </div>
</div>
<div class="section-full content-inner-2" style="background: url({{ asset('/frontend/img/bg1.png') }});">
    <div class="wrapper">
        <div class="row">
            <div class="col-md-12">
                <div class="spon-head">
                    <h3>Requested Page Not Found.</h3>
                </div>
            </div>
        </div>



        

    </div>


</div>

@endsection