<?php
require('../../dbManager.php');

class DataManager
{
  const _ORIGINAL   = 0;
  const _ADJUSTED   = 1;
  
  public function getData($project_id, $sprint_id, $view=self::_ORIGINAL)
  {
    // get the burndown values
    $query = 'SELECT day, hours_left, points_left FROM TEST_TNC_burndown 
              WHERE projects_id='.$project_id.' AND sprint_id='.$sprint_id.'
              ORDER BY day';

    $dbMgr = new DatabaseManager('dev');
    $result = $dbMgr->executeQuery($query);

    $b_data = array();
    while( $ret = mysql_fetch_array($result, MYSQL_ASSOC) )
    {
        $b_data[] = $ret;
    }

    // get the sprint info
    $query = 'SELECT s.sprint_id, s.length, s.points_estimate, s.hours_estimate, s.added_hours, s.added_points, p.name FROM TEST_TNC_sprint s, TNC_projects p 
              WHERE s.projects_id='.$project_id.' AND s.sprint_id='.$sprint_id.' AND p.id=s.projects_id';

    $s_data = $dbMgr->executeQueryWithResult($query);
    $dbMgr->closeConnection();

    if($view == self::_ADJUSTED)
    {
      $data = $this->adjustDataByView($b_data, $s_data);
    }
    else
    {
      $data = array($b_data, $s_data);
    }
    
    return $data;
  }
  
  public function splitSprintData($sprintData)
  {
    return array($sprintData['name'], $sprintData['sprint_id'], $sprintData['length'], $sprintData['points_estimate'], $sprintData['hours_estimate']);
  }
  
  private function adjustDataByView($burndown, $sprint)
  {
    $burndown_adj_data = array();
    $sprint_adj_data = array();
    
    foreach($burndown as $burndown_key => $burndown_value)
    {
      if($burndown_key == 'hours_left')
      {
        $burndown_adj_data[$burndown_key] = $burndown_value + $sprint['added_hours'];
      }
      elseif($burndown_key == 'points_left')
      {
         $burndown_adj_data[$burndown_key] = $burndown_value + $sprint['added_points'];
      }
      else
      {
        $burndown_adj_data[$burndown_key] = $burndown_value;
      }
    }
    
    foreach($sprint as $sprint_key => $sprint_value)
    {
      if($sprint_key == 'hours_estimate')
      {
        $sprint_adj_data[$sprint_key] = $sprint_value + $sprint['added_hours'];
      }
      elseif($sprint_key == 'points_estimate')
      {
         $sprint_adj_data[$sprint_key] = $sprint_value + $sprint['added_points'];
      }
      else
      {
        $sprint_adj_data[$sprint_key] = $sprint_value;
      }
    }
    
    return array($burndown_adj_data, $sprint_adj_data);
  }
}