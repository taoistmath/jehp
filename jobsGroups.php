<?php
class JobsGroups {
    public function getJenkinsGrpLst() {
        if(file_exists('groups.ini')) {
            return parse_ini_file('groups.ini');
        } else {
            exit('Create groups.ini');
        }
    }
    
    public function getStatusXml() {
        if (file_exists('results/status.xml')) {
            return simplexml_load_file('results/status.xml');
        } else  {
            exit('results/status.xml does not exist');
        }
    }

    function getJobStatus($results, $jobName) {
        $status = "";
        foreach ($results as $jobInfo):
            $name=$jobInfo->name;
            $color=$jobInfo->color;
            if ($jobName == $name) {
                switch($color)  { 
                case 'red':
                     $status = "failed";
                     break;
                case 'red_anime': 
                    $status = "run_fail";
                    break;
                case 'aborted_anime': 
                    $status = "run_bail";
                    break;
                case 'aborted': 
                    $status = "bailed";
                    break;
                case 'blue_anime':
                    $status = "run_pass";
                    break;
                case 'blue': 
                    $status = "passed";
                    break;
                case 'notbuilt':
                    $status = "notbuilt";
                    break;
                case 'disabled':
                    $status = "disabledbuild";
                    break;
                case 'yellow':
                    $status = "unstable";
                    break;
                case 'yellow_anime':
                    $status = "run_unstable";
                    break;
                default:
            }
        }
        endforeach;
        if($status == "") {
            $status = "not_found";
        }
        return $status;
    }
}
?>