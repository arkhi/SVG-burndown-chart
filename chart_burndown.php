<?php
include("DataManager.php");
include("GraphManage.php");

echo chartBurndown($_GET['pid'], $_GET['sid'], $_GET['view']);

function chartBurndown($project_id, $sprint_id, $view=0)
{
  $dataMgr = new DataManager();
  
  list($burndownData, $sprintData) = $dataMgr->getData($project_id, $sprint_id, $view);
  array($sprintName, $sprintIdNumber, $sprintLength, $sprintPointsEstimate, $sprintTaskEstimate) = $dataMgr->splitSprintData($sprintData);
  
  $graphMgr = new GraphManager(1000, 2000);
  $viewBox = $graphMgr->getViewBoxDimensions();
  
  list($graphHeight, $graphWidth, $graphMargin) = $graphMgr->getGraphDimensions();
  list($unitPoint, $unitDay) = $graphMgr->getUnitPoints($heightBase, $widthBase);
  
  $burndownPointsData = $graphMgr->getPlotPoints($burndownData, 'points_left', $sprintPointsEstimate, $sprintHoursEstimate, $sprintLength, false);
  $burndownTaskData = $graphMgr->getPlotPoints($burndownData, 'hours_left', $sprintHoursEstimate, $sprintHoursEstimate, $sprintLength, true);
 
}
