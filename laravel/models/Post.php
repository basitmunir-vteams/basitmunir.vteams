<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;

class Post extends Eloquent implements UserInterface, RemindableInterface {

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
	*	Funciton is responsibile to validate the form Data
	*/

	public function isValid(){
		// mkaing validations
		$validator = Validator::make($this->attributes, static::$rules);
		
		// check if validation passes
		if($validator->passes()){

			return true;

		} else {

			// setting up error messages.
			$this->errors = $validator->messages();
			return false;
		}
	}
}
