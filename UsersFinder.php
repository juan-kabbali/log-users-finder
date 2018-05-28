<?php
/**
 * Created by PhpStorm.
 * User: Kabbali
 * Date: 28/05/2018
 * Time: 4:12 PM
 */

include "Assets/Regex.php";
include "Assets/CommandOptions.php";

// GET THE LOGS DIRECTORY
// |-- to test locally
// $logs_dir = 'logs/';
// |-- to production environments
$logs_dir = '/usr/local/ezproxy/docs/logs/';

// GET THE LOGS FORMAT, BY DEFAULT IT WILL BE .log BUT THERE ARE SOME CASES WHERE THEY ARE NOT
$logs_format = '*.log';

// GET USER FIELD POSITION INSIDE A SINGLE LOG LINE
$uf = 3;

// ASSURE TO RUN IT AS ROOT
if (posix_getuid() != 0) {
    echo "ERROR: You must run this script as super user \n";
    exit(1);
}

echo "################################################ \n";
echo "Running USERS FINDER \n";
echo "################################################ \n";

// CATCHING INPUTS
// |-- --help
if (in_array($HELP, $argv)) {
    echo "USERS FINDER Options: \n";
    echo "\t --directory: to specify the directory where the logs are placed. Default value: $logs_dir \n";
    echo "\t --format: to specify the logs format. Default value: $logs_format \n";
    echo "\t --uf: to specify the user field position inside a single log line. Default value: $uf \n";
    exit(0);
}

// |-- --directory
if (in_array($DIR, $argv)) {
    $logs_dir = $argv[array_search($DIR, $argv) + 1];
    echo "LOGS DIRECTORY --> $logs_dir \n";
}

// |-- --format
if (in_array($FORMAT, $argv)) {
    $logs_format = $argv[array_search($FORMAT, $argv) + 1];
    echo "LOGS FORMAT --> $logs_format \n";
}

// |-- --uf
if (in_array($USER_FIELD_POSITION, $argv)) {
    $uf = $argv[array_search($USER_FIELD_POSITION, $argv) + 1];
    echo "USER FIELD POSITION --> $uf \n";
}

// GET ALL THE FILES THAT MATCH WITH DIR AND LOGS FORMAT
foreach (glob($logs_dir . $logs_format) as $file) {

    // CHECK IT THEY ARE READEBLES
    if (is_readable($file)) {

        // OPEN EACH FILE
        $logs = fopen($file, "r") or die("Unable to open file");
        echo "################################################ \n";
        echo "\t $file read successfully \n";
        echo "################################################ \n";

        // THIS VAR CONTAINS THE STRING OF EACH READ LOG LINE FILE
        $read_lines = array();

        // THIS VAR STORAGE NON DUPLICATED USERS
        $founded_users = '';

        // WE GET EACH LINE TO REPLACE WITH REGEX MATCHES WHILE IT EXISTS
        while ($line = fgets($logs)) {

            // WE APPLY REGEX AND STORAGE IT RESULTS IN $MATCHES
            preg_match_all(Regex::$LINE_PATTERN, $line, $matches, PREG_SET_ORDER, 0);

            // WE STORAGE EACH FOUNDED USER TO VERIFY IF HE IS DUPLICATED. IF NOT, WE PUSH IT IN $FOUNDED_USERS
            $tmp_user = $matches[$uf][0];
            if(!in_array($tmp_user, $read_lines)){
                array_push($read_lines, $tmp_user);
                $founded_users = $founded_users . $tmp_user . "\n";
                echo $tmp_user . " \n";
            }
        }

        // CREATE THE NEW LOG FILE WITH ITS ORIGINAL NAME WITH
        $new_log = fopen($file . ".users", "w");

        // WE WRITE IT WITH THE WHOLE CONCATENATED STRING
        fwrite($new_log, $founded_users);
        echo $file . "users wrote successfully \n";
        fclose($logs);
    }
}
