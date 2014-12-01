<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * SMS Client for services of ClickATell
 *
 * PHP version 5
 *
 * @copyright     Copyright (c) 2013, Louie Miranda
 * @author        Louie Miranda <lmiranda@gmail.com>
 */
class Sms {

    //API Credentials
    protected $User         = '';
    protected $Password     = '';
    protected $API_id       = '';
    protected $API_url      = 'http://api.clickatell.com';
    protected $Session_id;

    //container of recipients
    protected $Recipients   = array();

    /**
     * Public Function that sends the SMS
     *
     * @param recipients string
     * The list of cellphone numbers seperated by the "," character in one single string
     *
     * @param message
     * The message in on single string
     *
     */
    public function send($recipients, $message){

        //for lazy js coders :)
        if(substr($recipients, -1) == ','){
            $recipients = substr($recipients, 0, -1);
        }

        //validation for numbers
        if(trim($recipients) == ''){
            return array('ERROR' => 'There are no recipients!');
            die;
        }

        //validation for message
        if(trim($message) == ''){
            return array('ERROR' => 'There are no message!');
            die;
        }

        $f_recipients   = explode(',', $recipients);
        $f_message      = str_replace(' ', '+', trim($message));

        //trim message to 160 characters only
        if(strlen($f_message) > 160){
            $f_message = substr($f_message, 0, 160);
        }

        return $this->api_send($f_recipients, $f_message);
    }

    /**
     * Private function that contacts the api and truly sends the SMS
     *
     * @param recipients array()
     * A simple array of cellphone numbers
     *
     * @param message string
     * A string message which the " " is replaced into "+"
     *
     */
    private function api_send($recipients, $message){

        if ($this->authenticate()){

            //send statuses
            $send_status = array();

            foreach($recipients as $number){
                $send_url   = "$this->API_url/http/sendmsg?session_id=$this->Session_id&to=$number&text=$message&from=God";

                //do the actual sending
                $send_call  = $this->file_call($send_url);
                $response   = explode(":",$send_call);

				//print_r($response);

                if ($response[0] == "ID") {
                    $send_status[$number] = 'Message Sent Successfully!';
                } else {
                    $send_status[$number] = 'Message Sending Failed!';
                    print_r($response);
                }
            }

            return $send_status;
        }

        //authentication failed
        return array('Error' => 'API authentication failed. Please check API credentials.');
    }

    private function authenticate(){

        //auth url
        $auth_url   = "$this->API_url/http/auth?user=$this->User&password=$this->Password&api_id=$this->API_id";

        //make an auth call
        $auth_call  = $this->file_call($auth_url);
        $response   = explode(":",$auth_call);

        //if success return our session id
        if($response[0] == "OK"){
            $this->Session_id = trim($response[1]);
            return true;
        }

        //else throw the error
        return false; //'Authentication failure, please contact our SysAd : ' . $response[0];

    }

    private function allowedIP(){

        $return = true;

        if(!in_array($_SERVER['REMOTE_ADDR'], array(
            '127.0.0.1',
            '192.168.1.120'
        ))){
            $return = false;
        } else {
            return true;
        }

        if(substr($_SERVER['REMOTE_ADDR'], 0, 10) != '127.0.0' || substr($_SERVER['REMOTE_ADDR'], 0, 10) != '10.0.0'){
            $return = false;
        } else {
            return true;
        }

        return $return;
    }

    private function file_call($URL)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,"{$URL}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);

        $result=curl_exec ($ch);
        curl_close ($ch);

        return $result;
    }

}