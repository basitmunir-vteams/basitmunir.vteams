<?php

class UsersController extends \BaseController {

	protected $user,$post; //object for users model

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	
	public function __construct(){
		$this->user = New User;
		$this->post = New Post;
	}

	public function getIndex()
	{
		return Redirect::to('users/dashboard');
	}

	/**
	* show user Dashboard where user can view his posts posted so far.
	*
	*
	*/
	public function getDashboard(){
		if(Auth::check()) {

			$data = $this->post->where('user_id','=', Auth::user()->user_id)->paginate(10);
		
			return View::make('users/dashboard')->with(array('posts'=>$data, 'title'=>'Dashboard') ) ;

		} else {
			return Redirect::to('users/login');
		}
	}

	/*
	* function to show login form to login in the system
	*/
	public function getLogin(){

		if(!Auth::check()) {

			return View::make('users.login', array('title'=>'Login'));

		} else {

			return Redirect::to('users/dashboard');

		}
	}

	/*
	*	function to verify Identity of user who attempt to login.
	*/
	public function postLogin() {

		$email = Input::get('email');
		$password = Input::get('password');

		// $user = new User;
		// $user->username = 'myusername';
		// $user->email= Input::get('email');
		// $user->password = Hash::make(Input::get('password'));
		// $user->save();
		// exit;
		

		if(Auth::attempt(array( 'email' => $email, 'password' => $password) ) ) {
			
			
			if(Auth::user()->status != 3){
				return Redirect::to('users/dashboard');
			} else {
				return Redirect::to('admin');
			}

		} else {
			return Redirect::to('users/login')->withInput();
		}
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function postSignup(){
		

		if(!Auth::check()) {
			$input = Input::all();
			//$this->user->fill(Input::all())
			
			if(!$this->user->fill($input)->isValid()) {
			
				return Redirect::back()->withInput()->withErrors($this->user->errors);//'Validation failed';
			
			} else {
				
				$this->user->save();
				Session::flash('message', 'User Successfully added!');

				return Redirect::to('users/login')->with('message', 'Thanks for registering! Please Verify Your Email Address');

			}
		} else {
			return Redirect::route('users/dashboard');
		}
	}

	/*shows registration form*/

	public function getSignup()
	{
		if(Auth::check()) {
			
			Redirect::to('users/dashboard');

		} else {
			return View::make('users.signup',array('title'=>'signup'));

		}	
		
	}

	/*
	* function used to display edit form where user can change his name address phone number tec
	*/
	public function getProfile(){
		if(Auth::check()) {
			$user = $this->user->find(Auth::user()->user_id);

			return View::make('users.profile', array('title'=>'Manage Profile', 'user'=>$user));

		} else {
			return Redirect::to('users/login');

		}	
	}

	/*
	* update Profiles variables.
	*/
	public function postProfile(){
		if(Auth::check()) {
			$input = Input::all();
			//$this->user->fill(Input::all())
			
			if(!$this->user->fill($input)->isValid(true)) {
			
				return Redirect::back()->withInput()->withErrors($this->user->errors);//'Validation failed';
			
			} else {
				$this->user->find(Auth::user()->user_id)->save();

				Session::flash('message', 'Record Have been updated');

				return Redirect::to('users/profile');

			}
		} else {
			return Redirect::to('users/login');
		}
	}

	/*
	*	Function used to reset password.
	*/
	public function getResetpassword(){
		if(Auth::check()) {
			return View::make('users.resetpassword', array('title'=>'Reset Password'));
		} else {
			return Redirect::to('users/login');
		}
	}

	/*
	*	Function used to handle/ update password.
	*/
	public function postResetpassword(){
		if(Auth::check()) {
			$input = Input::all();
			//$this->user->fill(Input::all())
			//check if submitted old password is correct
			$user = Auth::attempt(array('email'=>Auth::user()->email, 'password'=>Input::get('old_password')));

			//if it is correct then perform further actions.
			if($user) {

				//if both new pasword and repassword are correct then ok update password other wise just go back and try again.
				if(Input::get('new_password') == Input::get('re_password')) {
					// updating password.
					$this->user->where('user_id', '=', Auth::user()->user_id)->update(array('password'=>Hash::make(Input::get('new_password'))));

					/* 
					*	restarting sessions. because laravel destroy user session if session values changes.
					* 	as password values changes so it destroy session we need to restart the session.
					*/
					Auth::attempt(array('email'=>Auth::user()->email, 'password'=>Input::get('new_password')));

					//setting up session message.
					Session::flash('message', 'Password have been updated');
					
					return Redirect::to('users/dashboard');

				} else {
					
					Session::flash('danger', 'Password must be matched');
					return Redirect::back();
				}
				
			} else {
			
				Session::flash('danger', 'Old Password is not Correct');
				return Redirect::back();

			}
		} else {
			
			return Redirect::to('users/login');
		}
	}

	public function getLogout(){
		Auth::logout();
    	return Redirect::to('users/login')->with('message', 'Your are now logged out!');
	}


}
