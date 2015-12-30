<?php
class Culprit {
    function getCulprit($jobName) { 
      $con = new JenkinsConfig;             
      if($xml = simplexml_load_file('https://'.$con->getUser().':'.$con->getPass().'@'.$con->getSite().'/job/'.$jobName.'/lastFailedBuild/api/xml')) {
          if(isset($xml->culprit->fullName))  {
            $culprit = $xml->culprit->fullName;
          } else {
            $culprit = "No culprit returned";
          }
      } else {
          $culprit = "No last failed build returned";
      }
      return $culprit;
    }
}
?>