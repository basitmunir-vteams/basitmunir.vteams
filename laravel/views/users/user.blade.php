@extends('layouts.default')
@section('content')
	<div class="row">
       
        <div class="col-md-12">
          
          <h1>All Users</h1>
          <table class="table table-striped">
          		<tr>
          			<th>Id</th>
          			<th>User Name</th>
          			
          			<th>Action</th>
          		</tr>
          		@if($users->count()) 
	          		@foreach($users as $user)
	          			<tr>
	          				<td>{{ $user->id}}</td>
	          				
	          				<td>{{ link_to("/users/{$user->username}", $user->username) }}</td>

	          				

	          				<td>
	          					{{ link_to("/users/delete/{$user->id}", 'Delete') }}
	          					{{ link_to("/users/edit/{$user->id}", 'Edit') }}
	          				</td>

	          			</tr>
	          		@endforeach
	          	@else 
	          		<tr> <td colspan="4">No User Found</td></tr>
	          	@endif
          </table>
        </div>
      </div>
@stop