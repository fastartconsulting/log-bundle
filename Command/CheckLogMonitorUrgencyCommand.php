<?php

namespace FAC\LogBundle\Command;

use DateTime;
use EmailBundle\Entity\Email;
use EmailBundle\Service\EmailService;
use http\Exception;
use FAC\LogBundle\Document\LogMonitor;
use FAC\LogBundle\Service\LogMonitorService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Twig_Environment;
use Symfony\Component\Translation\TranslatorInterface;
use FAC\LogBundle\Service\LogService;
use Utils\LogUtils;

class CheckLogMonitorUrgencyCommand extends ContainerAwareCommand {

    private $logMonitorService;
    private $emailService;
    /** @var Twig_Environment $templating */
    private $templating;

    /** @var TranslatorInterface $translator */
    private $translator;

    /** @var LogService $logService */
    private $logService;

    public function __construct(LogMonitorService $logMonitorService, EmailService $emailService, Twig_Environment $templating, TranslatorInterface $translator, LogService $logService) {
        $this->logMonitorService = $logMonitorService;
        $this->emailService = $emailService;
        $this->templating = $templating;
        $this->translator   = $translator;
        $this->logService   = $logService;
        parent::__construct();
    }

    protected function configure() {
        $this
            ->setName('check:log-monitor:urgency')
            ->setDescription('Check and report the most frequent errors with 500 levels.')
            ->setHelp('Check and report the most frequent errors with 500 levels.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $log_dir     = $this->getContainer()->getParameter('log_dir');
        $admin_email = $this->getContainer()->getParameter('meedox_admin_email');

        $report_file = 'report-log-monitor-urgency';
        $log_file    = 'email-queue_process';

        /** @var EmailService $emailService */
        $emailService = $this->emailService;

        $params = LogUtils::getLogParams(null,$this->translator, 0, "START COMMAND");
        $this->logService->createByCommand($params,$log_dir,"monitor",$report_file);

        $output->writeln("START");

        try {

            set_time_limit(0);

            $output->writeln('');
            $output->writeln('------------------------------');
            $output->writeln('LOGS MONITOR CHECK ERRORS URGENCY >= 500.');
            $output->writeln('------------------------------');
            $output->writeln('');

            $errors = $this->logMonitorService->getErrorsUrgency(true);

            if(count($errors) == 0) {
                $params['message'] = $this->translator->trans("NO ERRORS FOUND");
                $this->logService->createByCommand($params,$log_dir,"monitor",$report_file);
                $output->writeln("NO ERRORS FOUND");
                return;
            }

            $db_error_counter = 0;
            $counter = 0;
            $output->writeln("N. LOG MONITOR: ".count($errors));
            $body = '';

            $creation = new DateTime();
            $creation->setTimestamp(time());

            /** @var LogMonitor $error */
            foreach($errors as $error) {
                $counter++;

                $params['message'] = $this->translator->trans(strftime('%Y-%m-%d %H:%M:%S', $creation->getTimestamp())." URL: {$error->getUrl()} , METHOD: {$error->getMethod()} , MESSAGE: {$error->getMessage()}, BACKTRACE: {$error->getBacktrace()}");
                $this->logService->createByCommand($params,$log_dir,"monitor",$report_file);
                $output->writeln("URL: {$error->getUrl()} , METHOD: {$error->getMethod()} , MESSAGE: {$error->getMessage()}, BACKTRACE: {$error->getBacktrace()}");

                $firstHappened = '';
                if(!is_null($error->getFirstHappened())) {
                    $firstHappened = $error->getFirstHappened()->format('d/m/Y H:i:s');
                }
                $lastHappened = '';
                if(!is_null($error->getLastHappened())) {
                    $lastHappened = $error->getLastHappened()->format('d/m/Y H:i:s');
                }

                $body .= "\n-------------------------------------\n";
                $body .= "ERROR OCCURRED: {$error->getCount()} times \n LEVEL: {$error->getLevel()} \n URL: {$error->getUrl()} \n METHOD: {$error->getMethod()} \n MESSAGE: {$error->getMessage()} \n BACKTRACE: {$error->getBacktrace()} \n";
                $body .= "FIRST HAPPENED: {$firstHappened} times \n LAST HAPPENED: {$lastHappened} \n";
                $body .= "-------------------------------------\n\n\n";

                sleep(1);
                if($counter >= 25)
                    break;
            }

            try {
                $params['message'] = $this->translator->trans("TRY TO SEND");
                $this->logService->createByCommand($params,$log_dir,"monitor",$report_file);

                $output->writeln("TRY TO SEND");

                try {
                    $output->writeln("CREATION EMAIL");
                    $queueMail = $emailService->emailEnqueue(
                        $admin_email,
                        "LogMonitor check errors URGENCY",
                        $this->templating->render(
                            "email/log_monitor.email.twig",
                            array(
                                'body' => $body
                            )
                        ),
                        null,
                        null,
                        Email::TYPE_LOGMONITOR_CHECK
                    );

                    if(is_array($queueMail)) {
                        $channel = $this->logMonitorService->getChannel(500);
                        $this->logMonitorService->trace($channel, 500, "error.save", $queueMail,true);
                    }

                    if ($queueMail) {
                        $params['message'] = $this->translator->trans(strftime('%Y-%m-%d %H:%M:%S', $creation->getTimestamp())." SENT");
                        $this->logService->createByCommand($params,$log_dir,"monitor",$report_file);
                        $output->writeln("SENT");


                        /** @var LogMonitor $error */
                        foreach($errors as $error) {
                            if(!$this->logMonitorService->forceDelete($error)) {
                                $db_error_counter++;

                                $params['message'] = $this->translator->trans("DB NOT UPDATED CORRECTLY (ID: {$queueMail->getId()})");
                                $this->logService->createByCommand($params,$log_dir,"monitor",$log_file);

                                $output->writeln("ERROR");
                            }
                        }
                    }
                    else {
                        $params['message'] = $this->translator->trans("SENDING ERROR");
                        $this->logService->createByCommand($params,$log_dir,"monitor",$log_file);
                        $output->writeln("NOT SENT");
                    }
                } catch (\Exception $e) {

                    $params['message'] = $this->translator->trans("BODY FATAL ERROR: ".json_encode($e));
                    $this->logService->createByCommand($params,$log_dir,"monitor",$report_file);

                    $output->writeln("BODY FATAL ERROR");
                }
            } catch (\Exception $e) {
                $params['message'] = $this->translator->trans("TRY TO SEND FATAL ERROR: ".json_encode($e));
                $this->logService->createByCommand($params,$log_dir,"monitor",$report_file);
            }
        } catch (Exception $e) {
            $params['message'] = $this->translator->trans("CHECK LOG MONITOR FATAL ERROR: ".json_encode($e));
            $this->logService->createByCommand($params,$log_dir,"monitor",$report_file);
        }

        $output->writeln("DONE");
    }
}