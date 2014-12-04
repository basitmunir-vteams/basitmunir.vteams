<?php

class AdminController extends \BaseController {

	protected $user, $post, $admin;
	/**
     * The layout that should be used for responses.
     */
    protected $layout;
	
    public function __construct(){
    	$this->user = new User;
    	$this->post = new Post;
    	$this->admin= New Admin;
    }

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getIndex()
	{
		if(Auth::check() && Auth::user()->status == 3 ) {

			$data = $this->admin->usersList();

			return View::make('admin.users')->with(array('users'=>$data, 'title'=>'Manage User') ) ;

		} else {
			return Redirect::to('users/login');
		}

		return "asdf";
	}

	/**
	 * Display a listing of the users.
	 *
	 * @return Response
	 */
	public function getUsers()
	{
		if(Auth::check() && Auth::user()->status == 3 ) {

			$data = $this->admin->usersList();

			return View::make('admin.users')->with(array('users'=>$data, 'title'=>'Manage User') ) ;

		} else {
			return Redirect::to('users/login');
		}

	}

	/**
	 * Display a listing of the users.
	 *
	 * @return Response
	 */
	public function getPosts()
	{
		if(Auth::check() && Auth::user()->status == 3 ) {

			$data = $this->admin->postsLists();

			return View::make('admin.posts')->with(array('posts'=>$data, 'title'=>'Manage Posts') ) ;

		} else {
			return Redirect::to('users/login');
		}

		
	}

	/*
	* Function used to delete post
	*/
	public function getDeletepost($id) {
		if(Auth::check() && Auth::user()->status == 3) {
			$post = $this->post->find($id);
			
			if(file_exists(public_path().'/uploads/'.$post->photo_name)) {
				unlink(public_path().'/uploads/'.$post->photo_name);
			} 

			$post = $this->post->find($id)->delete();

			return Redirect::to('posts')->with('message', 'Post have been deleted');

		} else {

			return Redirect::to('users/login');

		}	
	}

	/*
	* function used to display edit form where user can change his name address phone number tec
	*/
	public function getProfile(){
		if(Auth::check() && Auth::user()->status == 3) {
			$user = $this->user->find(Auth::user()->user_id);

			return View::make('admin.profile', array('title'=>'Manage Profile', 'user'=>$user));

		} else {
			return Redirect::to('users/login');

		}	
	}

	/*
	* update Profiles variables.
	*/
	public function postProfile(){
		if(Auth::check() && Auth::user()->status == 3) {
			$input = Input::all();
			//$this->user->fill(Input::all())
			
			if(!$this->user->fill($input)->isValid(true)) {
			
				return Redirect::back()->withInput()->withErrors($this->user->errors);//'Validation failed';
			
			} else {
				$this->user->find(Auth::user()->user_id)->save();

				Session::flash('message', 'Record Have been updated');

				return Redirect::to('admin/profile');

			}
		} else {
			return Redirect::to('users/login');
		}
	}

	/*
	*	Function used to reset password.
	*/
	public function getResetpassword(){
		if(Auth::check() && Auth::user()->status == 3) {
			return View::make('admin.resetpassword', array('title'=>'Reset Password'));
		} else {
			return Redirect::to('users/login');
		}
	}

	/*
	*	Function used to handle/ update password.
	*/
	public function postResetpassword(){
		if(Auth::check() && Auth::user()->status == 3) {
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
					
					return Redirect::to('admin/index');

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

	/*
	*	Function used to Remove User from system.
	**/
	public function getBlockuser($id) {
		if(Auth::check() && Auth::user()->status == 3 ) {
			$this->user->find($id)->delete();
			return Redirect::to('admin/users');
		} else {
			return Redirect::to('users/login');
		}

	}

}
