<?php
include("burndown_chart_b.php");

echo graphBurndown($_GET['pid'], $_GET['sid'], $_GET['view']);

function graphBurndown($project_id, $sprint_id, $view=0)
{
  $grapher = new BurndownChart($project_id, $sprint_id);
  
  $grapher->graphData($view);
  
  return $grapher->render();
}