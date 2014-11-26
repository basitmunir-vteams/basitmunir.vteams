<?php

class UsersController extends \BaseController {

	protected $user ;

	public function __construct(User $user){
		$this->user = $user;
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$users= $this->user->all();

		return View::make('users.user',array('users'=>$users, 'title'=>'Manage Users'));
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		return View::make('users.create',array('title'=> 'Create New User'));
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$input = Input::all();
		//$this->user->fill(Input::all())

		if(!$this->user->fill($input)->isValid()) {
		
			return Redirect::back()->withInput()->withErrors($this->user->errors);//'Validation failed';
		
		} else {

			$this->user->save();

			return Redirect::route('users.index');

		}

		//dd(Input::all());
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		dd($id);
		$user= User::where('username','=',$id)->first();

		return View::make('users.show',array('user'=>$user, 'title'=>$user->username));
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

	
}
