<?php

    function get_values(){
      $params = parse_ini_file('config');
      return array_values($params);
    }

    function get_user(){
        return get_values()[0];
    }

    function get_pass(){
        return get_values()[1];
    }

    function get_site(){
        return get_values()[2];
    }

    function get_url(){
      return "https://".get_user().":".get_pass()."@".get_site()."/api/xml";
    }

    function prior_check($jobName) {
        if(file_exists('results/fails.xml')){
            $pastFails = simplexml_load_file('results/fails.xml');
        } else {
            touch('results/fails.xml');
            chmod('results/fails.xml', 0777);
            $pastFails = simplexml_load_file('results/fails.xml');
        }
        
        if($pastFails){
          foreach ($pastFails as $results) {
            if($jobName == $results->name) {
              return true;
            }
          }
        }
        return false;
    }

    function get_culprit($jobName) {      
      if($xml = simplexml_load_file('https://'.get_user().':'.get_pass().'@'.get_site().'/job/'.$jobName.'/lastFailedBuild/api/xml')) {
          if(isset($xml->culprit->fullName)){
            $culprit = $xml->culprit->fullName;
          } else {
            $culprit = "No culprit";
          }
      } else {
          $culprit = $jobName." appears to be in good shape.";
      }
      return $culprit;
    }

    function add_fail($jobName) {
      $culprit = get_culprit($jobName);

      $pastFails = simplexml_load_file('results/fails.xml');
      $failJobs = $pastFails->jobs;
      
      if($failJobs){
        $failJobs = $jobs->addChild('job');
        $jobs->addChild('name', $jobName);
        $jobs->addChild('culprit', $culprit);  
      }

      $failJobs->asXML('results/fails.xml');
      add_status_culprit($jobName);
    }

    function get_status_culprit($jobName) {
        $xml = simplexml_load_file('results/status.xml');

        foreach($xml->job as $segs){
          if(trim($segs->name) == trim($jobName)) {
            $culprit = $segs->culprit;
          }
        }
        if($culprit){
          return $culprit;    
        } else {
          return get_culprit($jobName);
        }
        
    }

    function add_status_culprit($jobName) {
      if (file_exists('results/status.xml')) {
        $currentJobs = simplexml_load_file('results/status.xml');
      } else {
        exit('Failed to open results/status.xml');
      }

      foreach($currentJobs->job as $segs){
        if(trim($segs->name) == trim($jobName)) {
          $seg=$segs->addChild('culprit',get_culprit($jobName));
        }
      }
      
      $currentJobs->asXML('results/status.xml');
    }

    function remove_fail($name){
        $pastFails = simplexml_load_file('results/fails.xml');
        $segs=$pastFails->xpath('//job[@name="$name"]');
        if (count($segs)>=1) {
          $seg=$segs[0];
        }

        $pastFails->asXML('results/fails.xml');
    }

    function prior_dump($jobName) {
        if(prior_check($jobName)){
            remove_fail($jobName);
        }
    }

    function get_job_status($results, $jobName){
        $status = "";
        foreach ($results as $jobInfo):
            $name=$jobInfo->name;
            $color=$jobInfo->color;
            if ($jobName == $name) {
                if ($color == 'red') {
                    $status = "failed";
                    if(!prior_check($name)){
                        add_fail($name);
                    }
                    break;
                } elseif ($color == 'red_anime') {
                    $status = "run_fail";
                    if(!prior_check($name)){
                        add_fail($name);
                    }
                    break;
                } elseif ($color == 'aborted_anime') {
                    $status = "run_bail";
                    break;
                } elseif ($color == 'aborted') {
                    $status = "bailed";
                    break;
                } elseif ($color == 'blue_anime') {
                    $status = "run_pass";
                    break;
                } elseif ($color == 'blue') {
                    $status = "passed";
                    prior_dump($name);
                    break;
                } elseif ($color == 'notbuilt') {
                    $status = "notbuilt";
                    break;
                } elseif ($color == 'disabled') {
                    $status = "disabledbuild";
                    break;
                } elseif ($color == 'yellow') {
                    $status = "unstable";
                    break;
                } elseif ($color == 'yellow_anime') {
                    $status = "run_unstable";
                    break;
                }
            }
        endforeach;
        if ( $status=="" ) {
            $status = "not_found";
        }
        return $status;
    }

    $url = get_url();
    if($xml = file_get_contents($url)){
        if(!file_exists('results/status.xml'))
        {
            mkdir('results', 0777, true);
        }
        file_put_contents('results/status.xml' , $xml);
    } else {
        exit('Could not get xml');
    }


    $finalPage = 'json/allJobs.json';
    $grpList = parse_ini_file('groups.ini');

    if (file_exists('results/status.xml')) {
        $jobs = simplexml_load_file('results/status.xml');
    } else {
        exit('Failed to open results/status.xml');
    }

    $groups = "";
    $groups .= "\"groups\": [\n";

    $jobList = "";
    $jobList = "\"jobs\": [\n";

    $curKey = '';
    $pass = false;
    $count = 1;
    $isAJobRunning = false;
    $worstJobStatus = 0;
    $curJobStatus = 0;
    $statusArray = array("passed", "run_pass", "notbuilt", "disabledbuild", "bailed", "run_bail", "unstable", "run_unstable", "failed", "run_fail");
    foreach($grpList as $key => $block) {
        $subcount = 1;
        $isAJobRunning = false;
        $worstJobStatus = 0;
        $curJobStatus = 0;

        foreach($block as $index => $value) {
            $valStat = get_job_status($jobs,$value);
            $jobList .= "\t\t{\"name\": \"" . $value . "\", \"culprit\": \"" . get_status_culprit($value) . "\", \"status\": \"" . $valStat . "\", \"group\": \"" . $key . "\", \"subtabid\": \"". $count . $subcount .  "\"},\n";
            if ($curKey != $key) {
                $curKey = $key;
            }
            
            switch($valStat){
                case "passed":
                    $curJobStatus = 0;
                    break;
                case "run_pass":
                    $curJobStatus = 1;
                    $isAJobRunning = true;
                    break;
                case "notbuilt":
                    $curJobStatus = 2;
                    break;
                case "disabledbuild":
                    $curJobStatus = 3;
                    break;
                case "bailed":
                    $curJobStatus = 4;
                    break;
                case "run_bail":
                    $curJobStatus = 5;
                    $isAJobRunning = true;
                    break;
                case "unstable":
                    $curJobStatus = 6;
                    break;
                case "run_unstable":
                    $curJobStatus = 7;
                    $isAJobRunning = true;
                    break;
                case "failed":
                    $curJobStatus = 8;
                    break;
                case "run_fail":
                    $curJobStatus = 9;
                    $isAJobRunning = true;
                    break;
                default:
            }

            if($curJobStatus > $worstJobStatus) {
                $worstJobStatus = $curJobStatus;
            }

            $subcount = $subcount + 1;
        }

        if($isAJobRunning){
                switch($worstJobStatus) {
                    case 2:
                    case 3:
                    case 4:
                        $worstJobStatus=5;
                        break;
                    case 6:
                        $worstJobStatus=7;
                        break;
                    case 8:
                        $worstJobStatus=9;
                        break;
                }
        }

        $groups .= "\t\t{\"name\": \"" . $key . "\", \"status\": \"" . $statusArray[$worstJobStatus] . "\", \"count\": \"" . $count . "\"},\n";
        $count = $count + 1;
    }
    $groups = substr($groups, 0, -2); //cut off extra new line and comma at the end
    $jobList = substr($jobList, 0, -2); //cut off extra new line and comma at the end
    $groups .= "\n\t]\n";
    $jobList .= "\n\t]\n";
    $mainJson = "";
    $mainJson .= "{\n\t" . $groups . ",\n\t" . $jobList . "}\n";
    file_put_contents($finalPage,$mainJson);

?>
