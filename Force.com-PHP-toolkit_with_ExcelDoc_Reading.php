<?php 
date_default_timezone_set('Asia/Manila');

/** Include path **/
set_include_path(get_include_path() . PATH_SEPARATOR . '/Applications/MAMP/htdocs/Classes/');

require_once ('/Applications/MAMP/htdocs/Force.com-PHP-Toolkit/soapclient/SforcePartnerClient.php');

$mySforceConnection2 = new SforcePartnerClient();
$mySoapClient2 = $mySforceConnection2->createConnection("partner.wsdl.xml");
$mylogin2 = $mySforceConnection2->login("egie.gutierrez@ip-converge.com", "qA3PefrePrur");


/** PHPExcel_IOFactory */ include 'PHPExcel/IOFactory.php';


$inputFileName = '/Applications/MAMP/htdocs/SalesForceBilling/Basewith2015schedules.xlsx';
#$echo 'Loading file ',pathinfo($inputFileName,PATHINFO_BASENAME),' using IOFactory to identify the format<br />';
$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);


$rowNumX=$argv[1];
$rowNumY=$argv[2];

$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
$OpportunityIDs=array();
foreach ($sheetData as $rowNum=>$value)
{
#if (($rowNum>1) && ($rowNum<219))
if (($rowNum>=$rowNumX) && ($rowNum<=$rowNumY))
{
$OpportunityID=$value[A];
if ($A{$OpportunityID}==1)
{

}
else
{

array_push($OpportunityIDs,$OpportunityID);
$A{$OpportunityID}=1;
}
}
}

$OpportunityLineItemIDs=array();
foreach ($OpportunityIDs as $key=>$OpportunityID)
{
print "Resetting Schedules for Opportunity ID - $OpportunityID\n";
$query= "SELECT Id from OpportunityLineItem where OpportunityId='".$OpportunityID."'";
$response=$mySforceConnection2->query($query);
$queryResult= new QueryResult($response);
for ($queryResult->rewind(); $queryResult->pointer < $queryResult->size; $queryResult->next()) {
				$record=$queryResult->current();
				array_push($OpportunityLineItemIDs,$record->Id);
		}
}


$OpportunityLineItemSchedules=array();
			$updated_records=array();
$query="";
$response="";
$ScheduleIDs=array();
foreach ($OpportunityLineItemIDs as $OpportunityLineItemID)
{
$query= "SELECT Id from OpportunityLineItemSchedule where OpportunityLineItemId='".$OpportunityLineItemID."' and ScheduleDate >= 2015-01-01 and Revenue>0";
print "\n";
$response=$mySforceConnection2->query($query);
$queryResult= new QueryResult($response);

for ($queryResult->rewind(); $queryResult->pointer < $queryResult->size; $queryResult->next()) {

    $record = $queryResult->current();
array_push($ScheduleIDs,$record->Id);
}

}

$response=NULL;
$record=array();

$response= $mySforceConnection2->retrieve('Id, ScheduleDate ,Revenue','OpportunityLineItemSchedule',$ScheduleIDs);
$i=0;
foreach ($response as $record)
{
if ($i<200)
{
$updated_records[$i]=new SObject();
$updated_records[$i]->Id=$record->Id;
$updated_records[$i]->type='OpportunityLineItemSchedule';
$updated_records[$i++]->fields = array ('Revenue' => 0);
}
else
{
#skip
}



}

$response= $mySforceConnection2->update($updated_records);
foreach ($response as $result) {
    echo $result->id . " updated<br/>\n";
}



?>
