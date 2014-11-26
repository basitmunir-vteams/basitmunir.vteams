@extends('layouts.default')
@section('content')
	<div class="row">
       
        <div class="col-md-12">
          
          <h1>Create New User</h1>
          {{ Form::open(array('route'=>'users.store','method'=>'post', 'files'=>true, 'class'=>'form-horizontal')) }}
	         
      		<div class="row form-group">
      			{{ Form::label('username', 'User Name', array('class'=>'col-sm-2 control-label')) }}
      			<div class="col-sm-10">
      				{{ Form::text('username','', array('placeholder'=>'User Name','class' => 'form-control')) }}
              {{ $errors->first('username') }}
      			</div>
      		</div>
    
    
      		<div class="row form-group">
      			{{ Form::label('password', 'Password', array('class'=>'col-sm-2 control-label')) }}
      			<div class="col-sm-10">
      			{{ Form::password('password', array('placeholder'=>'Password','class' => 'form-control')) }}
            {{ $errors->first('password') }}
      			</div>
      		</div>

          <div class="row form-group">
            {{ Form::label('email', 'Email', array('class'=>'col-sm-2 control-label')) }}
            <div class="col-sm-10">
            {{ Form::text('email','', array('placeholder'=>'Email','class' => 'form-control')) }}
            {{ $errors->first('email') }}
            </div>
          </div>
    

     
      		<div class="row form-group text-center">
      			{{ Form::submit('Submit!', array('class' => 'btn btn-default')) }}
      		</div>
	      
          {{ Form::close() }}
        </div>
      </div>
@stop