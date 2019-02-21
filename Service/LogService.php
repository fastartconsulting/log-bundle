<?php

namespace FAC\LogBundle\Service;

use DateTime;
use http\Exception;
use FAC\LogBundle\Model\Log;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;
use UserBundle\Entity\User;
use Utils\LogUtils;

class LogService{

    /** @var AuthorizationCheckerInterface $authorization_checker */
    protected $authorization_checker;

    /** @var TranslatorInterface $translator */
    protected $translator;


    /**
     * LogService constructor.
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param TranslatorInterface $translator
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker, TranslatorInterface $translator) {
        $this->authorization_checker = $authorizationChecker;
        $this->translator            = $translator;
    }

    /**
     * Returns true if the logged user can POST logs
     * @return bool
     */
    public function canPost() {
        if($this->authorization_checker->isGranted('ROLE_USER') === TRUE)
            return true;

        return false;
    }

    /**
     * Return true if there are no errors
     * Returns array if there are errors
     * @param $params
     * @return array
     */
    public function logValidator($params) {
        $errors_string = array();
        if(!isset($params['level'])) {
            $errors_string[] = array(
                'content' => NULL,
                'field' => 'level',
                'message' => $this->translator->trans('require.level', array(), 'validators')
            );
        }
        else {
            if($params['level'] < 1){
                $errors_string[] = array(
                    'content' => $params['level'],
                    'field' => 'level',
                    'message' => $this->translator->trans('invalid.level', array(), 'validators')
                );
            }
        }

        if(!isset($params['message'])) {
            $errors_string[] = array(
                'content' => NULL,
                'field' => 'message',
                'message' => $this->translator->trans('require.message', array(), 'validators')
            );
        }
        else {
            if(strlen($params['message']) < 1){
                $errors_string[] = array(
                    'content' => $params['message'],
                    'field' => 'message',
                    'message' => $this->translator->trans('invalid.message', array(), 'validators')
                );
            }
        }

        if(!isset($params['url'])) {
            $errors_string[] = array(
                'content' => NULL,
                'field' => 'url',
                'message' => $this->translator->trans('require.url', array(), 'validators')
            );
        }
        else {
            if (strlen($params['url']) < 1) {
                $errors_string[] = array(
                    'content' => $params['url'],
                    'field' => 'url',
                    'message' => $this->translator->trans('invalid.url', array(), 'validators')
                );
            }
        }

        if(count($errors_string) > 0){
            return $errors_string;
        }

        return array();
    }

    /**
     * @param $log_params
     * @param $log_dir
     * @param User|null $user
     * @return array|Log
     */
    public function create($log_params, $log_dir, User $user=null) {
        try {
            $log = new Log();
            $log->setType($log_params['type']);
            $log->setLevel((int) $log_params['level']);
            $log->setMessage($log_params['message']);
            $log->setMethod($log_params['method']);
            $log->setUrl($log_params['url']);
            $log->setIp($log_params['ip']);
            $log->setUserAgent($log_params['user_agent']);

            $when = new DateTime();
            $when->setTimestamp(time());
            $log->setWhen($when);

            $idUser = 'null';
            if (!is_null($user)) {
                $idUser = $user->getId();
            }

            $log->setIdUser($idUser);
            $log->setTimestamp(time());

            if($log->getType() == 0) {
                $type = 'SYS';
            }
            else {
                $type = 'TRACK';
            }

            if(!file_exists($log_dir."reports/")) {
                mkdir($log_dir."reports/");
            }

            $report_file = $log_dir."reports/".strftime('%Y%m%d')."-".$type."-"."report.csv";
            $handle = fopen($report_file, "a+");

            fputcsv($handle,array_values($log->getOrderedListValues()));

            fclose($handle);
        } catch (Exception $e) {
            $exception = LogUtils::getFormattedExceptions($e);
            return $exception;
        }

        return $log;
    }


    public function createByCommand($log_params, $log_dir, $dir, $filename) {
        try {
            $log = new Log();
            $log->setType($log_params['type']);
            $log->setLevel((int) $log_params['level']);
            $log->setMessage($log_params['message']);
            $log->setMethod($log_params['method']);
            $log->setUrl($log_params['url']);
            $log->setIp($log_params['ip']);
            $log->setUserAgent($log_params['user_agent']);

            $when = new DateTime();
            $when->setTimestamp(time());
            $log->setWhen($when);

            $log->setIdUser('command');
            $log->setTimestamp(time());

            if(!file_exists($log_dir.$dir)) {
                mkdir($log_dir.$dir);
            }

            $report_file = $log_dir.$dir."/".strftime('%Y%m%d')."-".$filename.".csv";
            $handle = fopen($report_file, "a+");

            fputcsv($handle,array_values($log->getOrderedListValues()));

            fclose($handle);
        } catch (Exception $e) {
            $exception = LogUtils::getFormattedExceptions($e);
            return $exception;
        }

        return $log;
    }


}