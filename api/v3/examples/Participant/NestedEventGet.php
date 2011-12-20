<?php



/*
 use nested get to get an event
 */
function participant_get_example(){
$params = array( 
  'id' => 17,
  'version' => 3,
  'api.event.get' => 1,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'participant','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function participant_get_expectedresult(){

  $expectedResult = array( 
  'is_error' => 1,
  'error_message' => 'DB Error: no such field',
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* 
* testGetNestedEventGet and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3/ParticipantTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/