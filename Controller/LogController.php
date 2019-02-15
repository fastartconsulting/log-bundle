<?php

namespace LogBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use LogBundle\Document\LogMonitor;
use LogBundle\Model\Log;
use LogBundle\Service\LogMonitorService;
use LogBundle\Service\LogService;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Utils\ResponseUtils;

class LogController extends FOSRestController {

    /**
     * Register from frontend all system log to store to file.
     *
     * @Route("/private/log/sys", methods={"POST"})
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     required=true,
     *     type="string",
     *     description="Authorization token: Bearer <token>"
     * ),
     * @SWG\Response(
     *     response=403,
     *     description="The user has not rights to read.",
     * ),
     * @SWG\Response(
     *     response=400,
     *     description="The parameters are empty or invalid.",
     * ),
     * @SWG\Response(
     *     response=500,
     *     description="Error on saved occurred.",
     * ),
     * @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=Log::class)
     *     )
     * ),
     * @SWG\Tag(name="Logs")
     * @param   Request $request
     * @param   LogService $logService
     * @param   LogMonitorService $logMonitorService
     * @return  JsonResponse
     */
    public function createLogSysAction(Request $request, LogService $logService, LogMonitorService $logMonitorService) {
        $response = new ResponseUtils($this->get("translator"), $logMonitorService);

        $params = json_decode($request->getContent(), true);
        if(count($params) == 0){
            return $response->getResponse(array(), "parameters.invalid",400);
        }

        $errors = $logService->logValidator($params);
        if(count($errors) > 0) {
            return $response->getResponse($errors, "parameters.invalid",400);
        }

        $log_params = array(
            'type'      => Log::LOG_SYS,
            'level'     => $params['level'],
            'message'   => $params['message'],
            'method'    => $request->getMethod(),
            'url'       => $params['url'],
            'ip'        => $request->getClientIp(),
            'user_agent'=> $request->headers->get('User-Agent')
        );

        $log_dir = $this->container->getParameter('log_dir');

        $logCreate = $logService->create($log_params, $log_dir);
        if(is_array($logCreate)) {
            $logMonitorService->trace(LogMonitor::LOG_CHANNEL_QUERY, 500, "query.error", $logCreate);
            return $response->getResponse(array(), "error.save",500);
        }

        return $response->getResponse($logCreate->listSerializer(), "success.save", 200);
    }

    /**
     * Register from frontend all user track to store to file.
     *
     * @Route("/private/log/track", methods={"POST"})
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     required=true,
     *     type="string",
     *     description="Authorization token: Bearer <token>"
     * ),
     * @SWG\Response(
     *     response=403,
     *     description="The user has not rights to read.",
     * ),
     * @SWG\Response(
     *     response=400,
     *     description="The parameters are empty or invalid.",
     * ),
     * @SWG\Response(
     *     response=500,
     *     description="Error on saved occurred.",
     * ),
     * @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=Log::class)
     *     )
     * ),
     * @SWG\Tag(name="Logs")
     * @param   Request $request
     * @param   LogService $logService
     * @param   LogMonitorService $logMonitorService
     * @return  JsonResponse
     */
    public function createLogTrackAction(Request $request, LogService $logService, LogMonitorService $logMonitorService) {
        $response = new ResponseUtils($this->get("translator"), $logMonitorService);

        $params = json_decode($request->getContent(), true);
        if(count($params) == 0){
            return $response->getResponse(array(), "parameters.invalid",400);
        }

        $errors = $logService->logValidator($params);
        if(count($errors) > 0) {
            return $response->getResponse($errors, "parameters.invalid",400);
        }

        $log_params = array(
            'type'      => Log::LOG_TRACK,
            'level'     => $params['level'],
            'message'   => $params['message'],
            'method'    => $request->getMethod(),
            'url'       => $params['url'],
            'ip'        => $request->getClientIp(),
            'user_agent'=> $request->headers->get('User-Agent')
        );

        $log_dir = $this->container->getParameter('log_dir');

        $logCreate = $logService->create($log_params, $log_dir);
        if(is_array($logCreate)) {
            $logMonitorService->trace(LogMonitor::LOG_CHANNEL_QUERY, 500, "query.error", $logCreate);
            return $response->getResponse(array(), "error.save",500);
        }

        return $response->getResponse($logCreate->listSerializer(), "success.save", 200);
    }
}
