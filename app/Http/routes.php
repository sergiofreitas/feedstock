<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return redirect('/profile');
});

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => ['web']], function () {
	Route::get('auth/authorize', ['as' => 'oauth.authorize.get', 'middleware' => ['check-authorization-params', 'auth'], function() {
	   $authParams = Authorizer::getAuthCodeRequestParams();

	   $formParams = array_except($authParams,'client');
	   $formParams['client_id'] = $authParams['client']->getId(); 
	   $formParams['scope'] = implode(config('oauth2.scope_delimiter'), array_map(function ($scope) {
	       return $scope->getId();
	   }, $authParams['scopes']));

	   return View::make('auth.authorization-form', ['params' => $formParams, 'client' => $authParams['client']]);
	}]);


	Route::post('auth/authorize', ['as' => 'oauth.authorize.post', 'middleware' => ['csrf', 'check-authorization-params', 'auth'], function() {

	    $params = Authorizer::getAuthCodeRequestParams();
	    $params['user_id'] = Auth::user()->id;
	    $redirectUri = '/';

	    // If the user has allowed the client to access its data, redirect back to the client with an auth code.
	    if (Request::has('approve')) {
	        $redirectUri = Authorizer::issueAuthCode('user', $params['user_id'], $params);
	    }

	    // If the user has denied the client to access its data, redirect back to the client with an error message.
	    if (Request::has('deny')) {
	        $redirectUri = Authorizer::authCodeRequestDeniedRedirectUri();
	    }

	    return Redirect::to($redirectUri);
	}]);


	Route::post('auth/access_token', function() {
	  Config::set('database.fetch', PDO::FETCH_CLASS);
	  return Response::json(Authorizer::issueAccessToken());
	});
});

Route::group(['middleware' => 'web'], function () {
    Route::auth();

    Route::get('/profile', 'HomeController@index');
});


$api = app('Dingo\Api\Routing\Router');

$api->version('v1', /*['middleware' => 'oauth'],*/ function ($api) {
	$api->get('/', '\App\Http\Controllers\ResourceController@index');

	$api->post('/', '\App\Http\Controllers\ResourceController@create');
});
