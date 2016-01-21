<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Dingo\Api\Routing\Helpers;

use App\Repositories\PostRepositoryEloquent;

class ResourceController extends Controller
{
	use Helpers;

	private $repository;

	public function __construct(PostRepositoryEloquent $repository)
	{
		$this->repository = $repository;
	}

	public function index()
	{
		return $this->repository->all();
	}

	public function create(Request $request)
	{
		return $this->repository->create( $request->all() );
	}
}
