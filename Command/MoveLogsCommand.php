<?php

namespace LogBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MoveLogsCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
            ->setName('logs:move')
            ->setDescription('Get all log files of day before and move them into another server.')
            ->setHelp('Get all log files of day before and move them into another server.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        set_time_limit(0);
        $log_dir = $this->getContainer()->getParameter('log_dir');
        $remote_path_log_track = $this->getContainer()->getParameter('remote_log_dir_track');
        $remote_path_log_sys = $this->getContainer()->getParameter('remote_log_dir_sys');

        if (!file_exists($remote_path_log_track)) {
            mkdir($remote_path_log_track, 0777, true);
        }

        if (!file_exists($remote_path_log_sys)) {
            mkdir($remote_path_log_sys, 0777, true);
        }

        $before_day_time = time();
        $before_day_str = date('Ymd', strtotime('-1 day', $before_day_time));

        //GET FILES TRACK AND SYS
        $files_track = preg_grep('~^'.$before_day_str.'-TRACK.*\.(log)$~', scandir($log_dir."reports/"));
        $files_sys = preg_grep('~^'.$before_day_str.'-SYS.*\.(log)$~', scandir($log_dir."reports/"));

        ///////////////////////////////////////////////////////////////////////////////

        $output->writeln("START");

        $output->writeln('');
        $output->writeln('------------------------------');
        $output->writeln('LOGS: MOVE LOG FILES OF DAY BEFORE INTO ANOTHER SERVER.');
        $output->writeln('------------------------------');
        $output->writeln('');

        $total_files_track = count($files_track);
        $total_files_sys = count($files_sys);
        $total_elements = $total_files_track+$total_files_sys;

        $progress = new ProgressBar($output, $total_elements);
        $progress->start();


        //CHECK IF THE FILES EXIST
        if($total_elements == 0) {
            $progress->finish();
            $output->writeln('');
            $output->writeln('');
            $output->writeln('------------------------------');
            $output->writeln('LOGS: THERE ARE NO LOG FILES TO MOVE');
            $output->writeln('------------------------------');
            $output->writeln('');

            $output->writeln("NO LOG FILES");
            return;
        }

        //CHECK IF THE TRACK FILES EXIST, IF YES MOVE
        if($total_files_track > 0) {
            foreach($files_track as $log_track) {
                $progress->advance();
                rename($log_dir."reports/".$log_track, $remote_path_log_track.$log_track);

                $output->writeln("MOVE LOG TRACK");
            }
        }

        //CHECK IF THE SYS FILES EXIST, IF YES MOVE
        if($total_files_sys > 0) {
            foreach($files_sys as $log_sys) {
                $progress->advance();
                rename($log_dir."reports/".$log_sys, $remote_path_log_sys.$log_sys);

                $output->writeln("MOVE LOG SYS");
            }
        }

        $progress->finish();
        $output->writeln('');
        $output->writeln('');
        $output->writeln('------------------------------');
        $output->writeln('LOGS: MOVING COMPLETETED');
        $output->writeln($total_files_track.": file/s track moved");
        $output->writeln($total_files_sys.": file/s sys moved");
        $output->writeln('------------------------------');
        $output->writeln('');

        $output->writeln("DONE");
    }
}