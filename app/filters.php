<?php

/*
|--------------------------------------------------------------------------
| Application & Route Filters
|--------------------------------------------------------------------------
|
| Below you will find the "before" and "after" events for the application
| which may be used to do any work before or after a request into your
| application. Here you may also register your custom route filters.
|
*/

App::before(function($request)
{
    $customer = Session::get('customer');
    if($customer==1) {
        Config::set('auth.model', 'Customer');
        Config::set('auth.table', 'tblAccount');
    }
	
	$reseller = Session::get('reseller');
    if($reseller==1) {
        Config::set('auth.model', 'Reseller');
        Config::set('auth.table', 'tblAccount');
    }
});


App::after(function($request, $response)
{
	//
});

/*
|--------------------------------------------------------------------------
| Authentication Filters
|--------------------------------------------------------------------------
|
| The following filters are used to verify that the user of the current
| session is logged into this application. The "basic" filter easily
| integrates HTTP Basic authentication for quick, simple checking.
|
*/

Route::filter('auth', function()
{
	if (Auth::guest())
	{
		if (Request::ajax())
		{
			return Response::make('Unauthorized', 401);
		}
		else
		{
            if ( !Request::ajax() && !Request::is('/') && !Request::is('login') && !Request::is('forgot_password') ) {
                if (Request::is('customer/*')){
                    return Redirect::to('/customer/login?redirect_to=' . Request::url() );
                }else  if (Request::is('reseller/*')){
                    return Redirect::to('/reseller/login?redirect_to=' . Request::url() );
                }else{
                    return Redirect::to('/login?redirect_to=' . Request::url() );
                }
            }
        }
	}else{

        $admin = Session::get('admin');
        $customer = Session::get('customer');
		$reseller = Session::get('reseller');


        $LicenceApiResponse = Company::getLicenceResponse();


        if ( !Request::ajax() && !Request::is('/') && !Request::is('login') && !Request::is('forgot_password') ) {

            //If Licence is not valid or expired
            if(isset($LicenceApiResponse) && $LicenceApiResponse['Status'] != 1  && !Request::is('company')){

                //Licence is not valid

                if($customer==1 || $reseller==1){
                    echo $LicenceApiResponse['Message'];
                    exit;
                }else{
                    return Redirect::to('/company');
                }

            }else{

                //Licence is valid

                if(Request::is('customer/*') || Request::is('reseller/*')){
                    if($admin==1){
                        return Redirect::to('/process_redirect');
                    }
                    //return Redirect::to('customer/profile');
                }
                else{
                    if($customer==1){
                        return Redirect::to('customer/profile');
                    }
					if($reseller==1){
                        return Redirect::to('reseller/profile');
                    }
                }
            }

        }
    }

});


Route::filter('auth.basic', function()
{
	return Auth::basic();
});

/*
|--------------------------------------------------------------------------
| Guest Filter
|--------------------------------------------------------------------------
|
| The "guest" filter is the counterpart of the authentication filters as
| it simply checks that the current user is not logged in. A redirect
| response will be issued if they are, which you may freely change.
|
*/

Route::filter('guest', function()
{
    /*if (Auth::check()) return Redirect::route('dashboard')->with('flash_notice', 'You are already logged in!');
    if (Auth::check()) return Redirect::to('/');*/

});

Route::filter('global_admin', function()
{
    //$global_admin = Session::get('global_admin');
   // if (empty($global_admin) ) return Redirect::to('/');
});

/*
|--------------------------------------------------------------------------
| CSRF Protection Filter
|--------------------------------------------------------------------------
|
| The CSRF filter is responsible for protecting your application against
| cross-site request forgery attacks. If this special token in a user
| session does not match the one given in this request, we'll bail.
|
*/

Route::filter('csrf', function()
{
	if (Session::token() != Input::get('_token'))
	{
		throw new Illuminate\Session\TokenMismatchException;
	}
});

/*
 * Roles : Permission to access url
 * */
Route::filter("role", function ()  {

/*    User::setCurrentUserRoles(); // Sets Session for different Roles (like isAdmin , isAccountManager, isCRM )
    $access = User::checkCurrentUserAccessPermission();  // Check permissions against role specified for current user.
    if(!$access){
        return 'You are not authorized!';
    }*/

});
