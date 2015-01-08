<?php

class PostsController extends \BaseController {
	protected $user,$post;

	public function __construct() {
		$this->user = New User;
		$this->post = New Post;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getIndex()
	{
		if(Auth::check()) {
			$data = $this->post->paginate(10);
			return View::make('posts.posts')->with(array('posts'=>$data, 'title'=>'Posts') ) ;

		} else {
			return Redirect::to('users/login');
		}
	}


	/**
	 * Show the form for creating a new Posts.
	 *
	 * @return Response
	 */
	public function getNew($id = false) {
		
		if(Auth::check()) {

			return View::make('posts.postform',array('title'=>'add New Post'));

		} else {

			return Redirect::to('users/login');

		}	
	}

	/*
	*	Function is responsible to handle submitted post, upload images attached with and save it in database. funciton will be called only when post request is submitted to this.
	*/
	public function postNew($id = false) {

		if(Auth::check()){

			$imgname = $this->uploadFile(); // uploading file if it exists 
			
			$input = Input::all(); 
			
			// setting up image fields name 
			if($id != false ) { // we are in edit mode.

				if( $imgname != NULL ) { 
					/*if imgname value is not mean some image have been uploaded.
					 then we first remove previous image and populate new field.*/

					if(file_exists(public_path().'/uploads/'.Input::get('photo_name'))) {
						unlink(public_path().'/uploads/'.Input::get('photo_name'));
					}

					$this->post->photo_name = $imgname; // filling property for add mode.
					$input['photo_name'] = $this->post->photo_name; // maintainging array for edit mode.
				} else {

					$this->post->photo_name = Input::get('photo_name');
					$input['photo_name'] = $this->post->photo_name;
				}
				
			} else {
				$this->post->photo_name = $imgname;
				$input['photo_name'] = $this->post->photo_name;
			}

			$this->post->user_id = Auth::user()->user_id;

			// validation check as well as filling property.
			if($this->post->fill($input)->isValid())
			{
				if($id == false ){ 
				    
				    $this->post->save(); // saving data in database.

				} else {
					
					$this->post->find($id)->update($input);
				}
			    
			    return Redirect::to('posts')->with('message', 'Post saved'); // Redirecting after successfull submission of post.
			}
			else
			{

			    return Redirect::back()->withInput()->withErrors($this->user->errors);//'Validation failed'
			}

		} else {

			return Redirect::to('users/login');

		}
	}


	/*
	*	Function used to edit post 
	*/
	public function getEdit($id) {
		if(Auth::check()) {
			$post = $this->post->find($id);

			return View::make('posts.editpost',array('title'=>'Edit Post', 'post'=>$post));

		} else {

			return Redirect::to('users/login');

		}	
	}

	/*
	* Function used to delete post
	*/
	public function getDelete($id) {
		if(Auth::check()) {
			$post = $this->post->find($id);
			
			if(file_exists(public_path().'/uploads/'.$post->photo_name)) {
				unlink(public_path().'/uploads/'.$post->photo_name);
			} else {
				dd("over hrere");
			}

			$post = $this->post->find($id)->delete();

			return Redirect::to('posts')->with('message', 'Post have been deleted');

		} else {

			return Redirect::to('users/login');

		}	
	}

	/*
	*	Function used to show post detail
	*/
	public function getView($id) {
		if(Auth::check()) {
			$post = $this->post->find($id);
			
			return View::make('posts.view', array('title'=> $post->title, 'post'=>$post));

		} else {

			return Redirect::to('users/login');

		}	

	}

	/*
	* Function used to upload File.
	*/
	protected function uploadFile(){
		if (Input::hasFile('image'))
		{
			$extension = Input::file('image')->getClientOriginalExtension();
			$destinationPath = public_path().'/uploads';
			$rnd_str = str_random(12);
			$tm = time();
			$filename=$tm.$rnd_str.'.'.$extension;
			$upload_success = Input::file('image')->move($destinationPath, $filename);
			if($upload_success) 
			{
   				return $filename;
			}
		}
		else
		{
			return NULL;
		}


	}
	

}
