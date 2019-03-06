<?php

namespace FAC\LogBundle\Utils;


use FAC\LogBundle\Model\Log;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

class Utils{

    public static function getFormattedExceptions(\Exception $e) {
        $exception = array();

        if(!is_null($e)) {
            $exception = array(
                'backtrace'        => $e->getTraceAsString(),
                'file'             => $e->getFile(),
                'line'             => $e->getLine(),
                'exceptionMessage' => $e->getMessage()
            );
        }

        return $exception;
    }


    public static function createFormattedExceptions($file,$line,$message) {
        $exception = array(
            'backtrace'        => '',
            'file'             => $file,
            'line'             => $line,
            'exceptionMessage' => $message
        );

        return $exception;
    }

    /**
     * Set a flag to identify the point of profiling
     * i.e.
     *
     * prof_flag("test")
     *
     * then echo the result
     *
     * prof_print();die;
     *
     * @param $str
     */
    public static function prof_flag($str) {
        global $prof_timing, $prof_names;
        $prof_timing[] = microtime(true);
        $prof_names[] = $str;
    }

    public static function prof_print() {
        global $prof_timing, $prof_names;
        $size = count($prof_timing);
        for($i=0;$i<$size - 1; $i++)
        {
            echo "<b>{$prof_names[$i]}</b><br>";
            echo sprintf("&nbsp;&nbsp;&nbsp;%f<br>", $prof_timing[$i+1]-$prof_timing[$i]);
        }
        echo "<b>{$prof_names[$size-1]}</b><br>";
    }



    public static function getLogParams (Request $request=null, TranslatorInterface $translator, int $level, string $message, $is_sys=false) {

        $params     = array();

        $params['type'] = Log::LOG_TRACK;

        if(!$is_sys && !is_null($request)){
            $method     = $request->getMethod();
            $url        = $request->getRequestUri();
            $ip         = $request->getClientIp();
            $user_agent = $request->headers->get('User-Agent');
            $referral   = $request->headers->get('referer');
        } else {
            $method     = null;
            $url        = null;
            $ip         = null;
            $user_agent = null;
            $referral   = null;

            $params['type'] = Log::LOG_SYS;
        }

        $params['level']      = $level;
        $params['message']    = $translator->trans($message);
        $params['method']     = $method;
        $params['url']        = $url;
        $params['ip']         = $ip;
        $params['user_agent'] = $user_agent;
        $params['referral']   = $referral;

        return $params;
    }

    static public function getCurrentTime () {

        $current_time = new \DateTime();
        $current_time->setTimestamp(time())->getTimestamp();

        return $current_time;
    }

    public static function checkId($id){

        if(is_null($id))
            return false;

        if(!is_numeric($id))
            return false;

        if($id < 1)
            return false;

        return true;
    }

    public static function checkNum($val) {

        if(is_null($val))
            return false;

        if(!is_numeric($val))
            return false;

        return true;
    }

    public static function domainExists($email){
        $domain = explode('@', $email);
        $arr= dns_get_record($domain[1],DNS_MX);
        if(!empty($arr)) {
            return true;
        }

        return false;
    }

    public static function checkEmailString($str) {
        if(is_null($str)) {
            return false;
        }

        if(!is_string($str)) {
            return false;
        }

        if(strlen($str) < 1 || strlen($str) > 255) {
            return false;
        }

        $pattern = "/^[a-zA-Z0-9.!#$%&’*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/";
        if(!preg_match($pattern, $str)) {
            return false;
        }

        if(!Utils::domainExists($str)) {
            return false;
        }

        return true;
    }

    public static function checkHashString($str) {
        if(is_null($str)) {
            return false;
        }

        if(!is_string($str)) {
            return false;
        }

        if(strlen($str) < 30 || strlen($str) > 500) {
            return false;
        }

        if(addslashes($str) != $str) {
            return false;
        }

        return true;
    }

    public static function checkPasswordString($str) {
        if(is_null($str)) {
            return false;
        }

        if(!is_string($str)) {
            return false;
        }

        if(strlen($str) < 2 || strlen($str) > 255) {
            return false;
        }

        return true;
    }

}