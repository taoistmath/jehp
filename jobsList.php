<?php
class JobsList {
    private $jobList = "\"jobs\": [\n";
    private $jobListRed = "";
    private $jobListYellow = "";
    private $jobListGreen = "";

    public function setJobListings($value, $culprit, $valStat, $key, $count, $subcount) {
        return "\t\t{\"name\": \"" . $value . "\", \"culprit\": \"" . $culprit . "\", \"status\": \"" . $valStat . "\", \"group\": \"" . $key . "\", \"subtabid\": \"". $count . $subcount .  "\"},\n";   
    }  

    public function jobConcat() {
        $this->jobListGreen = substr($this->jobListGreen, 0, -2); 
        $this->jobList .= $this->jobListRed . $this->jobListYellow . $this->jobListGreen;
        $this->jobList .= "\n\t]\n"; 
        return $this->jobList;
    }  

    public function jobListings() {
        $count = 1;
        $curJobStatus = 0;
        $jobgrp = new JobsGroups;
        $grpList = $jobgrp->getJenkinsGrpLst();
        $jobs = $jobgrp->getStatusXml();
        $culp = new Culprit;

        foreach($grpList as $key => $block) {
            $subcount = 1;
            $isAJobRunning = false;
            $worstJobStatus = 0;
            $curJobStatus = 0;

            foreach($block as $index => $value) {
                $valStat = $jobgrp->getJobStatus($jobs,$value);

                switch($valStat) {
                    case "failed":
                    case "run_fail": 
                        $culprit = $culp->getCulprit($value);
                        $this->jobListRed .= $this->setJobListings($value, $culprit, $valStat, $key, $count, $subcount);
                        break;
                    case "passed": 
                    case "run_pass":
                        $culprit = ""; 
                        $this->jobListGreen .= $this->setJobListings($value, $culprit, $valStat, $key, $count, $subcount);   
                        break;
                    default: 
                        $culprit = $culp->getCulprit($value);
                        $this->jobListYellow .= $this->setJobListings($value, $culprit, $valStat, $key, $count, $subcount);  
                        break;
                }
                $subcount = $subcount + 1;
            }
            $count = $count + 1;
        }
    }       
}
?>