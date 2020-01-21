<?php

class ExactController extends \BaseController {

    public function index() {
        $data = Input::all();
        $CompanyID  = User::get_companyID();
        $TOKEN_URL  = ExactAuthentication::TOKEN_URL;
        $AUTH_URL   = ExactAuthentication::AUTH_URL;

        $ExactIntegration       = Integration::where(['CompanyID'=>$CompanyID,'Slug'=>'exact'])->first();
        $EXACT_CONFIGURATION    = IntegrationConfiguration::where(array('CompanyId'=>$CompanyID,"IntegrationID"=>$ExactIntegration->IntegrationID))->first();
        $EXACT_CONFIGURATION    = json_decode($EXACT_CONFIGURATION->Settings, true);
        $REDIRECT_URL           = CompanyConfiguration::get('WEB_URL', $CompanyID) . "/exact";

        if (!empty($EXACT_CONFIGURATION['ExactClientID']) && !empty($EXACT_CONFIGURATION['ExactClientSecret'])) {
            if (!isset($data['code'])) {
                $params = array(
                    'client_id' => $EXACT_CONFIGURATION['ExactClientID'],
                    'redirect_uri' => $REDIRECT_URL,
                    'response_type' => ExactAuthentication::RESPONSE_TYPE_AUTH
                );

                $AUTH_URL = $AUTH_URL . '?' . http_build_query($params, '', '&');

                header('Location: ' . $AUTH_URL, TRUE, 302);
                die('Redirect');
            } else {
                $post_data['code'] = $data['code'];
                $post_data['grant_type'] = ExactAuthentication::RESPONSE_TYPE_TOKEN;
                $post_data['redirect_uri'] = $REDIRECT_URL;
                $post_data['client_id'] = $EXACT_CONFIGURATION['ExactClientID'];//'eb6fff47-449a-4197-9325-d23e2723e996';
                $post_data['client_secret'] = $EXACT_CONFIGURATION['ExactClientSecret'];//'ngM1XePBx8kG';

                $ch = curl_init($TOKEN_URL);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data, '', '&'));
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));

                $result = curl_exec($ch);
                $result = json_decode($result, true);

                if (!empty($result) && !isset($result['error'])) {
                    $save_data['authorization_code'] = $post_data['code'];
                    $save_data['access_token'] = $result['access_token'];
                    $save_data['expires_in'] = $result['expires_in'];
                    $save_data['refresh_token'] = $result['refresh_token'];
                    $save_data['token_type'] = $result['token_type'];
                    $save_data['last_updated_at'] = date('Y-m-d H:i:s');
                    $save_data['CompanyID'] = $CompanyID;

                    $ExactAuth = ExactAuthentication::where(['CompanyID' => $CompanyID])->first();

                    if ($ExactAuth) {
                        $ExactAuth->update($save_data);
                    } else {
                        ExactAuthentication::insert($save_data);
                    }

                    Session::set('success_message','Integration Successful, Access Token Generated');
                    return Redirect::to('/integration');
                } else {
                    $error = isset($result['error']) ? $result['error'] : 'No response from Exact API';
                    Session::set('error_message',$error);
                    return Redirect::to('/integration');
                }
            }
        } else {
            Session::set('error_message','Integration Failed, No App Configuration found for Exact Integration, Please contact your Administrator.');
            return Redirect::to('/integration');
        }
    }

}