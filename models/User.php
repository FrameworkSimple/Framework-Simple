<?php

Class User extends Model
{
	
	public function beforeSave($user) {

		// the the before function
		parent::beforeSave($user);

		// if the password is set
		if(isset($user['password']))
		{
			
			// set the password to the encrpyted one
			$user['password'] = Core::encrypt($user['password']);
		
		}

		return $user;
		
	}

}