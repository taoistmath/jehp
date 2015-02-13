<?php
include ("jenkinsConfig.php");
include ("culprit.php");
include ("jobsList.php");
include ("groupsList.php");
include ("jobsGroups.php");

$conf = new JenkinsConfig;
$conf->writeToStatusXml();

$jobLst = new JobsList; 
$jobLst->jobListings();

$groupsLst = new GroupsList;
$groupsLst->groupsListings();

$mainJson = "";
$mainJson .= "{\n\t" . $groupsLst->groupFailOnly() . ",\n\t" . $groupsLst->groupGreenOnly() . ",\n\t" . $jobLst->jobConcat() . "}\n";
file_put_contents('json/allJobs.json',$mainJson);
?>

