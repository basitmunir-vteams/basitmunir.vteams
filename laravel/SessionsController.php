<?php

class SessionsController extends \BaseController {

	/*
	*
	* function create will display a login form the user to login in for dashboard.
	*/
	public function create(){

		// creating view for user to login
		return View::make('users.login',array('title'=> 'Login'));
	}
	
	/*
	* function will authenticate the user and  redirect to dashbaord page. else it will keep user at login page.
	**/
	public function store() {

	}

	/**
	* function used to destroy user session log him out. from the system.
	*/
	public function destroy() {

	}
}
