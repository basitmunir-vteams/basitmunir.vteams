@extends('layouts.default')
@section('content')
	<div class="row">
       
        <div class="col-md-12">
          
          <h1>{{$user->name}}</h1>
          <p>Email: {{ $user->email }} </p>
        </div>
      </div>
@stop