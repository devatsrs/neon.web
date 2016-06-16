<?php

class HomeController extends BaseController {
    var $dashboard_url = 'process_redirect';

    public function __construct() {
        $DefaultDashboard = '';
        if (!Auth::guest()){
            $DefaultDashboard = CompanySetting::getKeyVal('DefaultDashboard')=='Invalid Key'?'':CompanySetting::getKeyVal('DefaultDashboard');
        }
        if(Company::isRMLicence()){
            $this->dashboard_url = '/monitor';
            if(!empty($DefaultDashboard)){
                $this->dashboard_url = $DefaultDashboard;
            }
            if (!user::is_admin()) {
                if(!empty($DefaultDashboard) && User::checkCategoryPermission(getDashBoardController($DefaultDashboard),'All')){
                    $this->dashboard_url = $DefaultDashboard;
                }else {
					/*
                     * Priority on redirect
                     * 1. Dashboard
                     * 2. Account
                     * 3. Profile
                     * */

                    if(User::checkCategoryPermission('MonitorDashboard', 'All')) {
                        $this->dashboard_url = '/monitor';
                    }elseif (User::checkCategoryPermission('RmDashboard', 'All')) {
                        $this->dashboard_url = '/dashboard';
                    } elseif (User::checkCategoryPermission('SalesDashboard', 'All')) {
                        $this->dashboard_url = '/salesdashboard';
                    } elseif (User::checkCategoryPermission('BillingDashboard', 'All')) {
                        $this->dashboard_url = '/billingdashboard';
                    } elseif (User::checkCategoryPermission('Account', 'View')) {
                        $this->dashboard_url = '/accounts';
                    } else {
                        $this->dashboard_url = '/users/edit_profile/' . User::get_userID();
                    }
                }
            }

        }elseif(Company::isBillingLicence()) {
            $this->dashboard_url = '/billingdashboard';
            if (!empty($DefaultDashboard) && User::checkCategoryPermission(getDashBoardController($DefaultDashboard), 'All')) {
                $this->dashboard_url = $DefaultDashboard;
            } else {
                if (User::checkCategoryPermission('BillingDashboard', 'All')) {
                    $this->dashboard_url = '/billingdashboard';
                } elseif (User::checkCategoryPermission('SalesDashboard', 'All')) {
                    $this->dashboard_url = '/salesdashboard';
                } elseif(User::checkCategoryPermission('MonitorDashboard', 'All')) {
                    $this->dashboard_url = '/monitor';
                } elseif (User::checkCategoryPermission('Account', 'View')) {
                    $this->dashboard_url = '/accounts';
                } else {
                    $this->dashboard_url = '/users/edit_profile/' . User::get_userID();
                }
            }
        }
    }

    public function home() {

        if(Auth::check()){
            return Redirect::to('/accounts');
        }else{
			create_site_configration_cache();			
            return View::make('user.login');
        }

    }

    public function forgot_password() {

        return View::make('user.forgot_password');
    }

    public function doforgot_password() {


        if (Request::ajax()) {

            $data = Input::all();
            $email = $data['email'];
            $user_reset_link = '';
            $User = new User();
            $user = $User->get_user_by_email($email);

            if (empty($user)) {
                echo json_encode(array("status" => "failed", "message" => "Email is not found, Please enter correct email."));
            } else {

                //$password = str_random(4);
                //$user->password = Hash::make($password);
                $remember_token = str_random(32);
                $user->remember_token = $remember_token;
                $user->save();
                $user_reset_link = URL::to('/reset_password')."?remember_token=".$remember_token;
                $user->user_reset_link = $user_reset_link;
                $data = array();
                $data['companyID'] = $user['CompanyID'];
                $CompanyName = Company::getName($data['companyID']);
                $data['Firstname'] = $user['FirstName'];
                $data['Lastname'] = $user['Lastname'];
                $data['EmailTo'] = $user['EmailAddress'];
                $data['CompanyName'] = $CompanyName;
                $data['Subject'] = 'Forgot Password!';
                $data['user_reset_link'] = $user_reset_link;
                $result = sendMail('emails.auth.forgot_password',$data);
                /*  $data = array('user' => $user);
                $result = Mail::send('emails.auth.forgot_password', $data, function($message) use ($user) {
                                    $message->to($user->EmailAddress, $user->Firstname . ' ' . $user->Lastname)->subject('Forgot Password!');
                                });*/
                if ($result['status'] == 1) {
                    echo json_encode(array("status" => "success", "message" => "Please check your email, reset password link will expire in 24 hours"));
                } else {
                    echo json_encode(array("status" => "failed", "message" => "Email sending failed, Please try again later"));
                }
            }
            exit;
        }
    }

    public function doLogin() {

        // Remove old session
        Session::flush();
        Auth::logout();

        if (Request::ajax()) {
            $data = Input::all();
            if(isset($data['user']) && $data['user'] == 'super') {
                //Check if Global Admin
                Config::set('auth.model', 'GlobalAdmin');
                if (GlobalAdmin::is_global_user($data)) {
                    $redirect_to = URL::to("/global_user_select_company");
                    Session::set("global_admin", 1 );
                    echo json_encode(array("login_status" => "success", "redirect_url" => $redirect_to));
                    return;
                }
            }
            //if Normal User
            if (Auth::attempt(array('EmailAddress' => $data['email'], 'password' => $data['password'] ,'Status'=> 1 )) && NeonAPI::login()) {
                User::setUserPermission();
				create_site_configration_cache();
				$query_data =  parse_url($_SERVER['HTTP_REFERER']);
				if(isset($query_data['query'])){parse_str($query_data['query']);}
				if(!isset($redirect_to)){
                	$redirect_to = URL::to($this->dashboard_url);
				}				
                if(isset($data['redirect_to'])){
                    $redirect_to = $data['redirect_to'];
                }
                echo json_encode(array("login_status" => "success", "redirect_url" => $redirect_to));
                return;
            } else {
                Session::flush();
                Auth::logout();
                echo json_encode(array("login_status" => "invalid"));
                return;
            }
        }
    }
	
    public function dologout() {
		NeonAPI::logout();
        Session::flush();
        Auth::logout();
        return Redirect::to('/login')->with('message', 'Your are now logged out!');
    }

    public function registration(){

        return View::make('user.registration');

    }

    public function doRegistration(){

        $data = Input::all();

        $data['Phone'] = str_replace("_","",$data['Phone']);
        $data['Phone'] = str_replace("-","",$data['Phone']);
        $data['Phone'] = str_replace("(","",$data['Phone']);
        $data['Phone'] = str_replace(")","",$data['Phone']);
        $data['Phone'] = str_replace(" ","",$data['Phone']);

        $rules = array(
            'CompanyName' => 'required|min:3|unique:tblCompany,CompanyName',
            'FirstName' => 'required',
            'LastName' => 'required',
            'Email' =>  'required|min:5|unique:tblUser,EmailAddress',
            'Phone' => 'required|min:10',
            'Password' => 'required',
        );

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        $CompanyData['CompanyName'] = $data['CompanyName'];
        $CompanyData['FirstName']   = $data['FirstName'];
        $CompanyData['LastName']    = $data['LastName'];
        $CompanyData['Email']       = $data['Email'];
        $CompanyData['Phone']       = $data['Phone'];
        $CompanyData['Status']       =   1;


        $UserData['FirstName']      = $data['FirstName'];
        $UserData['LastName']       = $data['LastName'];
        $UserData['EmailAddress']   = $data['Email'];
        $UserData['Password']       = Hash::make($data['Password']);
        $UserData['Status']       =   1;
        $UserData['AdminUser']       =   1;
        $UserData['Roles']       =   'Admin';


        $company_created = Company::create($CompanyData);
        $CompanyID = DB::getPdo()->lastInsertId();

        $UserData['CompanyID']      = $CompanyID;
            $user_created    = User::create($UserData);


        if ( $user_created && $company_created ) {
            $result  = false;
            $taskBoard = ['CompanyID'=>$CompanyID,'BoardName'=>'Task Board','Status'=>1,'BoardType'=>CRMBoard::TaskBoard];
            CRMBoard::create($taskBoard);
            $admin_email = Config::get("app.super_admin_emails");
            Mail::send('emails.admin.registration', array("data"=>$data), function($message) use ($admin_email) {
                $message->to($admin_email['registration']['email'], $admin_email['registration']['from_name'])->subject('RM: Thanks for Registration!');
            });

            Mail::send('emails.auth.registration', array("data"=>$data), function($message) use ($data) {
                $message->to($data['Email'], $data['FirstName'] . ' ' . $data['LastName'])->subject('RM: Thanks for Registration!');
            });
            $failures  = Mail::failures();
            if (count($failures)==0) {
                return Response::json(array("status" => "success", "message" => "Please check your email, reset password link will expire in 24 hours"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Email sending failed, Please try again later"));
            }

        } else {
            return Response::json(array("status" => "failed"));
        }

        //return View::make('user.registration');

    }

    public function do_login_for_super_admin(){

        $data = Input::all();

        $user_ID = $data['User'];
        $user = User::find($user_ID);
        $redirect_to = URL::to($this->dashboard_url);
        if(isset($data['redirect_to'])){
            $redirect_to = $data['redirect_to'];
        }

        if(!empty($user) ){
            Auth::login($user);
            User::setUserPermission();
            echo json_encode(array("login_status" => "success", "redirect_url" => $redirect_to));
            return;
        } else {
            echo json_encode(array("login_status" => "invalid"));
            return;
        }
        exit;
    }

    public function reset_password() {
        $data = Input::all();
        //if any open reset password page direct he will redirect login page
        if(isset($data['remember_token']) && $data['remember_token'] != '')
        {
            return View::make('user.reset_password');
        }else{
            return Redirect::to('/');
        }
    }

    public function doreset_password() {
        if (Request::ajax()) {
            $data = Input::all();
            $remember_token = $data['remember_token'];
            if($data['password'] != $data['confirmpassword'])
            {
                echo json_encode(array("status" => "failed", "message" => "Password is not match."));
                exit;
            }
            $User = new User();
            $user = $User->get_user_by_remember_token($remember_token);
            if (empty($user)) {
                echo json_encode(array("status" => "failed", "message" => "Invalid Token."));
                exit;
            } else {
                $user->password = Hash::make($data['password']);
                $user->remember_token = 'NUll';
                $user->save();
                /*$data = array('user' => $user);
                $result = Mail::send('emails.auth.reset_password', $data, function ($message) use ($user) {
                    $message->to($user->EmailAddress, $user->Firstname . ' ' . $user->Lastname)->subject('Reset Password!');
                });*/
                $data = array();
                $data['companyID'] = $user['CompanyID'];
                $CompanyName = Company::getName($data['companyID']);
                $data['Firstname'] = $user['FirstName'];
                $data['Lastname'] = $user['Lastname'];
                $data['EmailTo'] = $user['EmailAddress'];
                $data['Subject'] = 'Reset Password!';
                $data['CompanyName'] = $CompanyName;
                $result = sendMail('emails.auth.reset_password',$data);
                if ($result['status'] == 1) {
                    echo json_encode(array("status" => "success", "message" => "Please check your email, Your password is reset"));
                } else {
                    echo json_encode(array("status" => "failed", "message" => "Email sending failed, Please try again later"));
                }
            }
            exit;
        }
    }
    public function process_redirect(){			
		 $redirect_to = isset($data['redirect_to'])?$data['redirect_to']:URL::to($this->dashboard_url);		
        return Redirect::to($redirect_to);
    }
}
