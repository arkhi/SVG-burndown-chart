<?php
include("DataManager.php");
include("GraphManager.php");

echo chartBurndown($_GET['pid'], $_GET['sid'], $_GET['view']);

function chartBurndown($project_id, $sprint_id, $view=0)
{
  $dataMgr = new DataManager();
  
  list($burndownData, $sprintData) = $dataMgr->getData($project_id, $sprint_id, $view);
  list($sprintName, $sprintIdNumber, $sprintLength, $sprintPointsEstimate, $sprintTaskEstimate) = $dataMgr->splitSprintData($sprintData);
  
  $graphMgr = new GraphManager(1000, 2000);
  $viewBox = $graphMgr->getViewBoxDimensions();
  
  list($graphHeight, $graphWidth, $graphMargin) = $graphMgr->getGraphDimensions();
  list($unitPoint, $unitDay) = $graphMgr->getUnitPoints($sprintTaskEstimate, $sprintLength);
  
  $burndownPointsData = $graphMgr->getPlotPoints($burndownData, 'points_left', $sprintPointsEstimate, $sprintTaskEstimate, $sprintLength, false);
  $burndownTaskData = $graphMgr->getPlotPoints($burndownData, 'hours_left', $sprintTaskEstimate, $sprintTaskEstimate, $sprintLength, true);
  list($globalEstimates, $localEstimates) = $graphMgr->getGlobalEstimates();
  $lastCoords = $graphMgr->getLastCoords();
  
  
  ob_start();
  include_once('burndown_template.php');
  
  $content = ob_get_contents();
  ob_end_clean();
 
  return $content;
}
