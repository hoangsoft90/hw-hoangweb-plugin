<?php
/**
 * Class HW_Shell
 */
abstract class HW_Shell extends HW_Core{
    /**
     * execute command line
     * @param $cmd
     */
    public function cmd_exec($cmd) {
        if(file_exists($cmd)) $cmd = "C:\\Windows\\System32\\cmd.exe /c {$cmd}";    //run .sh/.bat file

        /**/
        $descriptorspec = array(
            0 => array("pipe", "r"),   // stdin is a pipe that the child will read from
            1 => array("pipe", "w"),   // stdout is a pipe that the child will write to
            2 => array("pipe", "w")    // stderr is a pipe that the child will write to
        );
        flush();
        $process = proc_open($cmd, $descriptorspec, $pipes, realpath('./'), array());
        #echo "<pre>";
        if (is_resource($process)) {
            while ($s = fgets($pipes[1])) {
                print $s;
                flush();
            }
        }
        #echo "</pre>";
    }

    /**
     * get output of executation
     * @param $cmd
     */
    public function get_exec($cmd) {
        if(file_exists($cmd)) $cmd = "C:\\Windows\\System32\\cmd.exe /c {$cmd}";    //run .sh/.bat file
        $descriptorspec = array(
            array("pipe", "r"),  // stdin is a pipe that the child will read from    1 =>
            array("pipe", "w"),  // stdout is a pipe that the child will write to    2 =>
            array("file", "f:/error-output.txt", "a") // stderr is a file to   write to
        );
        $process = proc_open($cmd, $descriptorspec, $pipes);
        if (is_resource($process)) {    // $pipes now looks like this:    // 0 =>
            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            // It is important that you close any pipes before calling    // proc_close in order to avoid a deadlock    $return_value = proc_close($process);
            print_r($output);
        }
    }

    /**
     * realtime output from command
     * @param $cmd
     * @param $callback
     */
    public function realtime_exec($cmd, $callback=null) {

        @header('Content-Encoding: none;');

        set_time_limit(0);

        $handle = popen($cmd, "r");

        if (ob_get_level() == 0)
            ob_start();

        while(!feof($handle)) {

            $buffer = fgets($handle);
            $buffer = trim(htmlspecialchars($buffer));

            if($buffer) {
                //echo $buffer . "<br />";
                if(is_callable($callback)) call_user_func($callback, $buffer);
                else echo $buffer . "<br/>";
            }
            //echo str_pad('', 4096);

            ob_flush();
            flush();
            sleep(1);
        }

        pclose($handle);
        ob_end_flush();
    }
}