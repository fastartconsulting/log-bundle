<?php

namespace FAC\LogBundle\Service;

use DateTime;
use FAC\LogBundle\Document\LogMonitor;
use FAC\LogBundle\Repository\LogMonitorRepository;
use FAC\LogBundle\Utils\Utils;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class LogMonitorService {

    private $user = null;

    /** @var Request $request */
    private $request;

    /** @var TranslatorInterface $translator */
    private $translator;

    private $log_dir;

    /** @var LogService $logService */
    private $logService;

    /** @var ContainerInterface $container */
    private $container;
    private $repository;

    private $authorization_checker;

    ///////////////////////////////////////////
    /// CONSTRUCTOR

    /**
     * @param LogMonitorRepository $repository
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param LogService $logService
     * @param ContainerInterface $container
     */
    public function __construct(LogMonitorRepository $repository,
                                AuthorizationCheckerInterface $authorizationChecker,
                                LogService $logService,
                                ContainerInterface $container) {

        if(!is_null($container->get('security.token_storage')->getToken())) {
            $this->user   = $container->get('security.token_storage')->getToken()->getUser();
        }

        $this->request    = $container->get('request_stack')->getCurrentRequest();
        $this->translator = $container->get('translator');
        $this->log_dir    = $container->getParameter('log_dir');
        $this->repository = $repository;
        $this->container  = $container;
        $this->authorization_checker = $authorizationChecker;

        $this->logService = $logService;

    }

    /**
     * @param int $channel
     * @param string $method
     * @param int $level
     * @param string $url
     * @return string
     */
    public function init(int $channel, string $method, int $level, string $url) {

        $str = "";
        $str .= strtolower($channel);
        $str .= strtolower($method);
        $str .= strtolower($level);
        $str .= strtolower($url);
        $hash = md5($str);

        $logMonitor = $this->getByHash($hash);
        $when       = new DateTime();
        $when->setTimestamp(time())->getTimestamp();

        if(is_null($logMonitor)) {

            /** @var LogMonitor $logMonitor */
            $logMonitor = new LogMonitor();
            $logMonitor->setCount(0);
            $logMonitor->setFirstHappened($when);
        } else {
            $count = $logMonitor->getCount();
            $logMonitor->setCount((int) $count + 1);
            $logMonitor->setLastHappened($when);
        }

        $logMonitor->setChannel($channel);
        $logMonitor->setMethod(strtoupper($method));
        $logMonitor->setLevel($level);
        $logMonitor->setUrl($url);

        return $logMonitor;
    }

    /**
     * @param LogMonitor $logMonitor
     * @param array $params
     * @return string
     */
    public function setLogInformation(LogMonitor &$logMonitor, &$params) {

        $message  = isset($params['message'])  ? ((strlen($params['message']) > 1000) ? substr($params['message'],0,997).'...' : $params['message']) : '';
        $referral = isset($params['referral']) ? $params['referral'] : '' ;

        $logMonitor->setMessage($message);
        $logMonitor->setReferral($referral);

        return $logMonitor;

    }

    /**
     * @param LogMonitor $logMonitor
     * @param array $exceptions
     * @return string
     */
    public function setServerError(LogMonitor &$logMonitor, &$exceptions) {

        $backtrace = isset($exceptions['backtrace'])        ? ((strlen($exceptions['backtrace']) > 1000) ? substr($exceptions['backtrace'],0,997).'...' : $exceptions['backtrace']):'';
        $file      = isset($exceptions['file'])             ? $exceptions['file'] : '';
        $line      = isset($exceptions['line'])             ? $exceptions['line'] : '';
        $eMessage  = isset($exceptions['exceptionMessage']) ? $exceptions['exceptionMessage'] : '';

        $logMonitor->setBacktrace($backtrace);
        $logMonitor->setFile($file);
        $logMonitor->setLine($line);
        $logMonitor->setExceptionMessage($eMessage);

        return $logMonitor;

    }

    /**
     * @param int $channel
     * @param int $level
     * @param string $message
     * @param array $exceptions
     * @param bool $is_sys
     * @return bool|null
     */
    public function trace(int $channel, int $level, string $message, array $exceptions = array(), $is_sys=false) {


        if(!is_null($this->container->get('security.token_storage')->getToken())) {
            $this->user   = $this->container->get('security.token_storage')->getToken()->getUser();
        }

        $params = Utils::getLogParams($this->request,$this->translator, $level, $message, $is_sys);

        if($level >= 400){

            /** @var LogMonitor $logMonitor */
            $logMonitor = $this->init($channel, (string) $params["method"], $level, (string) $params["url"]);
            $this->setLogInformation($logMonitor, $params);

            if($level == 500 && count($exceptions) > 0) {
                $this->setServerError($logMonitor, $exceptions);
            }

            if(!$this->save($logMonitor)){
                return null;
            }
        }

        $logCreate = $this->logService->create($params, $this->log_dir, $this->user);
        if(is_array($logCreate)) {
            //$this->trace(LogMonitor::LOG_CHANNEL_QUERY, 500, "query.error", $logCreate);
            return null;
        }

        return true;
    }

    /**
     * @param int $status
     * @return int
     */
    public function getChannel(int $status){
        $channel = LogMonitor::LOG_CHANNEL_UNKNOWN;

        switch($status) {
            case 200:
                $channel = LogMonitor::LOG_CHANNEL_SUCCESS;
                break;
            case 201:
                $channel = LogMonitor::LOG_CHANNEL_SUCCESS;
                break;
            case ($status >= 400 && $status <= 500):
                $channel = LogMonitor::LOG_CHANNEL_WARNING;
                break;
            case 500:
                $channel = LogMonitor::LOG_CHANNEL_ERROR;
                break;
        }

        return $channel;
    }

    public function getErrors($is_sys=false) {
        $list = null;
        try{
            $list = $this->repository->findErrors(LogMonitor::LOG_MAX_COUNT);
        }
        catch (\Exception $e) {
            $exception = Utils::getFormattedExceptions($e);
            $this->trace(LogMonitor::LOG_CHANNEL_QUERY, 500, "query.error", $exception, $is_sys);
        }


        return $list;
    }


    public function getErrorsUrgency($is_sys=false) {
        $list = null;
        try{
            $list = $this->repository->findErrorsUrgency(LogMonitor::LOG_MAX_COUNT);
        }
        catch (\Exception $e) {
            $exception = Utils::getFormattedExceptions($e);
            $this->trace(LogMonitor::LOG_CHANNEL_QUERY, 500, "query.error", $exception, $is_sys);
        }


        return $list;
    }


}