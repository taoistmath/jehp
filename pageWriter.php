<?php
    $finalPage = 'quick_health.html';
    $grpList = parse_ini_file('groups.ini');

    if (file_exists('results/status.xml')) {
        $jobs = simplexml_load_file('results/status.xml');
    } else {
        exit('Failed to open results/status.xml');
    }

    function get_job_status($results, $jobName){
        $status = "";
        foreach ($results as $jobInfo):
            $name=$jobInfo->name;
            $color=$jobInfo->color;
            if ($jobName == $name) {
                if ($color == 'red') {
                    $status = "failed";
                    break;
                } elseif ($color == 'red_anime') {
                    $status = "run_fail";
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
                } 
		
            }
        endforeach;
        if ( $status=="" ) {
            $status = "not_found";
        }
        return $status;
    }

    function insertSubTabContent($jobsInGroup, $tabNumber){
        global $pageBody;
        $count = 1;
        foreach($jobsInGroup as $index => $value){
            $pageBody .= "\t\t\t\t\t<div class=\"tab-pane active in\" id=\"subtab" . $tabNumber . $count . "\">";
            //$pageBody .= "<p>" . $value . "</p>";   //for future content inside subtab
            $pageBody .= "</div>\n";
            $count = $count + 1;
        }

    }

    function insertTabContent($jobStatuses){
        $count = 1;
        global $pageBody, $grpList;
        foreach($grpList as $key => $block) {
            
            if($count == 1){
                $pageBody .= "\t\t<div class=\"tab-pane active in\" id=\"tab" . $count . "\">\n";
            }
            else{
                $pageBody .= "\t\t<div class=\"tab-pane\" id=\"tab" . $count . "\">\n";
            }
            
            //$pageBody .= "<div class=\"tab-pane active in\" id=\"set" . $count . "\">";
            $pageBody .= "\t\t\t<div class=\"tabbable tabs-left\">\n";
            $pageBody .= "\t\t\t\t<ul class=\"nav nav-tabs\">\n";
            $subcount = 1;
            foreach($block as $index => $value) {
                if($subcount == 1){
                    $pageBody .= "\t\t\t\t\t<li class =\"active " . $jobStatuses[$count-1][$subcount-1] ."\" >" . "<a href=\"#subtab" . $count . $subcount . "\">" . $value . "</a></li>\n";
                }
                else{
                    $pageBody .= "\t\t\t\t\t<li class =\"" . $jobStatuses[$count-1][$subcount-1] . "\">" . "<a href=\"#subtab" . $count . $subcount . "\">" . $value . "</a></li>\n";
                }
                //$pageBody .= "<li>" . "<a href=\"#subtab" . $count . $subcount . "\">" . $value . "</a></li>\n";
                $subcount = $subcount + 1;
            }

            $pageBody .= "\t\t\t\t</ul>\n";
            $pageBody .= "\t\t\t\t<div class = \"tab-content\">\n";
            insertSubTabContent($block, $count);
            $pageBody .= "\t\t\t\t</div>\n";
            $pageBody .= "\t\t\t</div>\n";
            $pageBody .= "\t\t</div>\n";
            $count = $count + 1;
        }
    }




    $pageBody = "<html> \n";
    $pageBody .= "<head> \n";
    $pageBody .= "<title>JEHP</title>\n";
    $pageBody .= "<meta http-equiv=\"refresh\" content=\"60; url=index.php\">\n";
    $pageBody .= "<link href=\"css/bootstrap.min.css\" rel=\"stylesheet\" media=\"screen\">\n";
    $pageBody .= "<style type=\"text/css\">\n";
    $pageBody .= "body {background-color:#000000;}\n";
    $pageBody .= "li a{text-align:center; color:#FFFFFF;text-shadow: 1px 1px 2px #000000;}\n";
    $pageBody .= "a:hover {text-align:center; color:#FFFFFF;text-shadow: 1px 1px 2px #000000;}\n";
    $pageBody .= "a:focus {text-align:center; color:#FFFFFF;text-shadow: 1px 1px 2px #000000;}\n";
    $pageBody .= ".passed {background-color:#2AC738;}\n";
    $pageBody .= ".failed {background-color:#FF0000;}\n";
    $pageBody .= ".bailed {background-color:#FFD600;}\n";
    $pageBody .= ".notbuilt {background-color:#FFD600;}\n";
    $pageBody .= ".disabledbuild {background-color:#FFD600;}\n";
    $pageBody .= ".unstable {background-color:#FFD600;}\n";

    $pageBody .= ".not_found {background-color:#FF7700;}\n";
    $pageBody .= ".run_pass {background-color:#2AC738;animation: blink 1s steps(5, start) infinite;";
    $pageBody .= "-webkit-animation: blink 1s steps(5, start) infinite;}\n";
    $pageBody .= ".run_fail {background-color:#FF0000;animation: blink 1s steps(5, start) infinite;";
    $pageBody .= "-webkit-animation: blink 1s steps(5, start) infinite;}\n";
    $pageBody .= ".run_bail {background-color:#FFD600;animation: blink 1s steps(5, start) infinite;";
    $pageBody .= "-webkit-animation: blink 1s steps(5, start) infinite;}\n";
    $pageBody .= "@keyframes blink { to { visibility: hidden; }}\n";
    $pageBody .= "@-webkit-keyframes blink { to { visibility: hidden; }}\n";
    $pageBody .= "</style>\n";
    $pageBody .= "</head>\n";
    $pageBody .= "<body>\n";
    $pageBody .= "<div class=\"tabbable tabs-left\">\n";
    $pageBody .= "\t<ul class=\"nav nav-tabs\">\n";

    $curKey = '';
    $pass = false;
    $count = 1;
    $jobStatuses = array();
    foreach($grpList as $key => $block) {
        $mainStat="passed";
        $newArray = array();
        foreach($block as $index => $value) {
            $valStat = get_job_status($jobs,$value);
            $newArray[] = $valStat;
            if ($curKey != $key) {
                $curKey = $key;
            }
            if ($valStat == "not_found") {
                $pageBody .= "\t\t<li id=\"" . $key . "\" class=\"not_found\">" . $value . "</li>\n";
            } else {
                switch($valStat){
                    case "run_pass":
                        if ($mainStat=="passed") {
                            $mainStat = $valStat;
                        }
                    case "notbuilt":
                        if ($mainStat=="passed" || $mainStat=="run_pass") {
                            $mainStat=$valStat;
                        }
                    case "disabledbuild":
                        if ($mainStat=="passed" || $mainStat=="run_pass") {
                            $mainStat=$valStat;
                        } 
                    case "bailed":
                        if ($mainStat=="passed" || $mainStat=="run_pass" || $mainStat=="notbuilt" || $mainStat=="disabled") {
                            $mainStat=$valStat;
                        }
                    case "run_bail":
                        if ($mainStat=="passed" || $mainStat=="run_pass" || $mainStat=="notbuilt" || $mainStat=="disabled" || $mainStat=="bailed") {
                            $mainStat=$valStat;
                        }
                    case "unstable":
                        if ($mainStat=="passed" || $mainStat=="run_pass" || $mainStat=="notbuilt" || $mainStat=="disabled" || $mainStat=="bailed" || $mainStat=="run_bail") {
                            $mainStat=$valStat;
                        }
                    case "run_fail":
                        if ($mainStat=="passed" || $mainStat=="run_pass" || $mainStat=="notbuilt" || $mainStat=="disabled" || $mainStat=="bailed" || $mainStat=="run_bail" || $mainStat=="unstable") {
                            $mainStat=$valStat;
                        }
                    case "failed":
                        $mainStat=$valStat;
               }
            }
        }

        $jobStatuses[] = $newArray;
        
        $pageBody .= "\t\t<li id=\"" . $key . "\" class=\"" . $mainStat . "\">" . "<a href=\"#tab" . $count . "\" data-toggle=\"tab\">" . $key . "</a></li>\n";
        $count = $count + 1;
    }

    $pageBody .= "\t</ul>\n";
    $pageBody .= "\t<div class=\"tab-content\">\n";
    insertTabContent($jobStatuses);
    $pageBody .= "\t</div>\n";
    $pageBody .= "</div>\n";
    $pageBody .= "<script src=\"http://code.jquery.com/jquery.js\"></script>\n";
    $pageBody .= "<script src=\"js/bootstrap.min.js\"></script>\n";


    $pageBody .= "<script>\n";
    $pageBody .= "$('.nav-tabs a').click(function (e) {\n";
    $pageBody .= "e.preventDefault();\n";
    $pageBody .= "$(this).tab('show');\n";
    $pageBody .= "});\n";
    $pageBody .= "</script>\n";

    $pageBody .= "</body>\n";
    $pageBody .= "</html>\n";
    file_put_contents($finalPage,$pageBody);
?>
