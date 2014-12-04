<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;

class Admin extends Eloquent implements UserInterface, RemindableInterface {

	use UserTrait, RemindableTrait;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'posts';
	
	protected $primaryKey = 'post_id';

	protected $fillable = array('user_id', 'title','description','photo_name','status');

	public $errors;
	
	public static $rules = array('title'=>'required','description'=>'required','photo_name'=>'required');

	 /* The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('password', 'remember_token');

	/*
	*	Funciton fetch users record along with there number of posts posted
	*/
	public function usersList() {
		
		return DB::table('users')
                     ->select(DB::raw('count(posts.user_id) as posts, users.email, users.username, users.user_id'))
                     ->leftJoin('posts', 'users.user_id', '=', 'posts.user_id')
                     ->where('users.status', '!=', '3')
                     ->groupBy('posts.user_id')
                     ->paginate(10);
	}


	/*
	*
	*/
	public function postsLists() {
		return DB::table('posts')
                     ->select(DB::raw('posts.*, CONCAT(users.firstname," ",users.lastname) as name'))
                     ->leftJoin('users', 'users.user_id', '=', 'posts.user_id')
                     ->paginate(10);
	}

	
}
