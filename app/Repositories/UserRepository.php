<?php

namespace App\Repositories;

class UserRepository extends Repository 
{
	function model()
	{
		return 'App\User';
	}
}