<?php
require('../../dbManager.php');

class BurndownChart
{
  private $project_id;
  private $sprint_id;
  private $burndown_data;
  private $sprint_data;
  private $graphCode;
  private $globalArray;
  
  const _ORIGINAL   = 0;
  const _ADJUSTED   = 1;
  const _GWIDTH     = 2000;
  const _GHEIGHT    = 1000;
  const _GMARGIN    = 150;
  
  public function __construct($project_id, $sprint_id)
  {
    $this->project_id = $project_id;
    $this->sprint_id  = $sprint_id;
    $this->graphCode = '';
  }
  
  public function render()
  { 
    return $this->graphCode; 
  }
  
  public function graphData($view)
  {
    $this->getData($view);
    $this->initializeGraph();
    $this->addTitleToGraph();
    $this->addGraphDefinitions();
    $this->addLegendsGrid();
    $this->chartStoryPoints();
    $this->chartTaskHours();
    $this->calculateEstimations();
    $this->graphCode .= '</svg>';
    
  }
  
  private function initializeGraph()
  {
    $width = _GWIDTH + 2 * _GMARGIN;
    $height = _GHEIGHT + 2 * _GMARGIN;
    $this->graphCode .= '<svg  version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
          preserveAspectRatio="xMidYMid meet"
          viewBox="0 0'.$width.' '.$height.'"';
  }
  
  private function addGraphTitle()
  {
    $this->graphCode .= '<title>burndown chart - sprint '.$this->sprint_data['sprint_id'].'</title>';
  }
  
  private function addGraphDefinitions()
  {
    $this-graphCode .= '<defs>
      <!-- filters -->
      <filter id="dropShadow">
        <feGaussianBlur in="SourceGraphic" stdDeviation="1" />
      </filter>
    </defs>';
  }
  
  private function addBaseGraphGrid()
  {
    $gridOptions = array( 'transform' => 'translate('._GMARGIN.','._GMARGIN.')');
    $baseGrid = $this->openGridTag('grid', $gridOptions);
    //horizontal lines: based on estimated hours
    for($i = 0; $i < $this->sprint_data['hours_estimate']; $i += 10)
    {
      $lineY = _GHEIGHT - $i * (_GHEIGHT / $this->sprint_data['hours_estimate']);
      $lineX = _GWIDTH;
      $lineOptions = array('stroke-dasharray' => '5,5');
      $baseGrid .= $this->getLineTag(0, $lineY, $lineX, $lineY, $lineOptions);
      $textOptions = array('text-anchor' => 'end', 'dominant-baseline' =>'middle');
      $baseGrid .= $this->getTextTag($i, -12, $lineY, $textOptions);
    }
    //vertical lines: based on days in sprint
    for($i = 0; $i < $this->sprint_data['days']; $i++)
    {
      $lineX = _GWIDTH - $i * (_GWIDTH / $this->sprint_data['days']);
      $lineY = _GHEIGHT;
      $lineOptions = array('stroke-dasharray' => '5,5');
      $baseGrid .= $this->getLineTag($lineX, 0, $lineX, $lineY, $lineOptions);
      if($i != 0)
      {
          $text = 'day '.$i;
          $textX = $i * (_GWIDTH / $this->sprint_data['days']);
          $textOptions = array('text-anchor' => 'middle');
          $baseGrid .= $this->getTextTag($text, $textX, -12, $textOptions);
      }
    }
    
    $baseGrid .= $this->buildIdealLineGrid(); 
    
    //build the frame
    $rectOptions = array('fill' => 'none', 'stroke' => '#000' );
    $baseGrid .= $this->getRectangleTag(0, 0, _GWIDTH, _GHEIGHT, $rectOptions);
    
    $baseGrid .= '</g>';
    
     $this->graphCode .= $baseGrid;
    
  }
  
  private function addGoalPoint()
  {
    $circOptions = array('fill' => '#090', 'stroke' => '#000', 'transform' => 'translate('._GMARGIN.', '._GMARGIN.')');
    $goalPoint = $this->addCircleTag(_GWIDTH, _GHEIGHT, 5, $circOptions);
    
    return $goalPoint;
  }
  
  private function addLegendsGrid()
  {
     $gridOptions = array( 'transform' => 'translate('._GMARGIN.','._GMARGIN.')');
     $legendsGrid = $this->openGridTag('legends', $gridOptions);
     $legendsGrid .= $this->getTextTag($this->sprint_data['hours_estimate'], -12, 0, array('fill'=>'#900', 'text-anchor' => 'end'));
     $legendsGrid .= $this->getTextTag('Sprint'.$this->sprint_data['sprint_id'], 12, (_GHEIGHT - 88), array('fill'=>'#999'));
     $legendsGrid .= $this->getTextTag('Hours'.$this->sprint_data['hours_estimate'], 12, (_GHEIGHT - 50), array('fill'=>'#900'));
     $legendsGrid .= $this->getTextTag('Points'.$this->sprint_data['points_esitmate'], 12, (_GHEIGHT - 12), array('fill'=>'#069'));
     $legendsGrid .= '</g>';
     
      $this->graphCode .= $legendsGrid;
  }
  
  private function chartStoryPoints()
  {
    $gridOptions = array( 'transform' => 'translate('._GMARGIN.','._GMARGIN.')');
    $storyPointGrid = $this->openGridTag('chart-us', $gridOptions);
    $dayUnit = _GWIDTH / $this->sprint_data['days'];
    $pointsUnit = _GHEIGHT / $this->sprint_data['hours_estimate'];
    $i = 0;
    
    foreach($this->burndown_data as $data_point)
    {
      $previousX      = ($i-1) * $dayUnit + $dayUnit;
      $x              = $i * $dayUnit + $dayUnit;
      
      $previousY      = (($i == 0) ? $this->sprint_data['points_estimate'] : $y ) * $pointsUnit;
      $y              = $data_point['points_left'] * $pointsUnit;
      $storyPointGrid .= $this->getLineTag($previousX, $previousY, $x, $y);
      
    }
    
    $i = 0;
    
    foreach($this->burndown_data as $data_point)
    {
      $x              = $i * $dayUnit + $dayUnit + 12;
      $y              = $data_point['points_left'] * $pointsUnit - 12;
      
      $storyPointGrid .= $this->getCircleTag($x, $y, 5);
      $storyPointGrid .= $this->getTextTag($data_point['points_left'], $x, $y);
      
    }
    
    $circY = ($this->sprint_data['hours_estimate'] - $this->sprint_data['points_estimate']) * $pointUnit;
    $textY = $circY - 12;
    $storyPointGrid .= $this->getCircleTag(0, $circY, 5);
    $storyPointGrid .= $this->getTextTag($data_point['points_estimate'], 12, $textY);
    $storyPointGrid .= '</g>';
    
    $this->graphCode .= $storyPointGrid;
  }
  
  private function chartTaskHours()
  {
    
    $gridOptions = array( 'transform' => 'translate('._GMARGIN.','._GMARGIN.')');
    $taskHoursGrid = $this->openGridTag('chart-tasks', $gridOptions);
    $dayUnit = _GWIDTH / $this->sprint_data['days'];
    $pointsUnit = _GHEIGHT / $this->sprint_data['hours_estimate'];
    $i = 0;
    foreach($this->burndown_data as $data_point)
    {
      $previousX      = ($i-1) * $dayUnit + $dayUnit;
      $x              = $i * $dayUnit + $dayUnit;
      
      $previousY      = (($i == 0) ? $this->sprint_data['hours_estimate'] : $y ) * $pointsUnit;
      $y              = $data_point['hours_left'] * $pointsUnit;
      $lineOptions = array('id' => 'day'.$i)
      $taskHoursGrid .= $this->getLineTag($previousX, $previousY, $x, $y, $lineOptions);
    }
    
    $i = 0;
    
    $previousHours      = $this->sprint_data['hours_estimate'];
    foreach($this->burndown_data as $data_point)
    {
      $previousX      = ($i-1) * $unitDay + $unitDay;
      $previousY      = (($i == 0) ? $this->sprint_data['hours_estimate'] : $y ) * $pointsUnit;
      $x              = $i * $dayUnit + $dayUnit + 12;
      $burnedHours    = $previousHours - $data_point['hours_left'];
      $y              = $data_point['hours_left'] * $pointsUnit - 12;
      $previousHours  = $data_point['hours_left'];
      
      $circOptions = array('id' => 'day'.$i);
      $taskHoursGrid .= $this->getCircleTag($x, $y, 5, $circOptions);
      $taskHoursGrid .= $this->getTextTag($data_point['hours_left'], $x, $y);
      
      $setOptions = array('attributeName' => 'opacity', 'to' => '1', 'begin' => 'day'.$i.'.mouseover;point'.$i-1.'.mouseover');
      $textAttribute =  $this->getSetAttributeTag($setOptions);
      $setOptions = array('attributeName' => 'opacity', 'to' => '0', 'begin' => 'day'.$i.'.mouseout;point'.$i-1.'.mouseout');
      $textAttribute .=  $this->getSetAttributeTag($setOptions);

      $text = $textAttribute.$burnedHours;
      $textOptions = array('opacity' => '0');
      $taskHoursGrid .= $this->getTextTag($text, $previousX + 12, $previousY - 12, $textOptions);
    }
    $taskHoursGrid .= $this->getCircleTag(0, 0, 5);
    $taskHoursGrid .= '</g>';
    
    $this->globalArray = array('x' => $x, 'y' => $y, 'previousX' => $previousX, 'previousY' => $previousY, 'burnedHours' => $burnedHours);
    
     $this->graphCode .= $taskHoursGrid;
  }
  
  private function calculateEstimations()
  {
    
    /* global estimation */
    $x = $this->globalArray['x'];
    $y = $this->globalArray['y'];
    $previousX = $this->globalArray['previousX'];
    $previousY = $this->globalArray['previousY'];
    
    $globalSlope      = $y/$x;
    $globalYIntercept = $y - $x * $globalSlope;
    $globalX          = _GWIDTH;
    $globalY          = $globalX * $globalSlope + $globalYIntercept;
    $globalDiff       = $globalY * 100 / _GHEIGHT - 100;

    if($globalY > _GHEIGHT) {
      $globalY    = _GHEIGHT;
      $globalX    = $globalY / $globalSlope - $globalYIntercept;
      $globalDiff = 100 - $globalX * 100 / _GWIDTH;
    }

    $globalRed    = abs(round(100 - $globalDiff)) == 0 ? '00' : 'ff';
    $globalGreen  = abs(round(100 - $globalDiff)) == 0 ? 187 : min( (1)*255, 210) - 75;
    $globalGreen  = $globalGreen < 0 ? '00' : digitNumber(dechex($globalGreen));
    $globalColor  = $globalRed.$globalGreen.'00';

    /* local estimation */
    $localSlope       = ($y - $previousY) / ($x - $previousX);
    $localYIntercept  = $y - $x * $localSlope;
    $localX           = _GWIDTH;
    $localY           = $localSlope * $localX + $localYIntercept;
    $localDiff        = $localY * 100 / _GHEIGHT - 100;

    if($localY > _GHEIGHT) {
      $localY     = _GHEIGHT;
      $localX     = ($localY - $localYIntercept) / $localSlope;
      $localDiff  = 100 - $localX * 100 / _GWIDTH;
    }

    /* points left */
    $globalDiffRound = abs(round($globalDiff));
    $localDiffRound = abs(round($localDiff));

    if($globalDiff == 0){
      $globalDiffLegend = $localDiffLegend = 'Success!';
    }
    else{
      $globalDiffLegend = $globalDiff < 0 ? '% left' : '% more';
      $localDiffLegend  = $localDiff < 0 ? '% left' : '% more';
    }

    $blink  = round(100) == 0 ? '' : '<animate attributeName="fill" values="#'.$deviationColor.';#f00;#'.$deviationColor.'" dur="1" repeatCount="indefinite" />';
    
    $this->chartGlobalEstimation($globalColor, globalX, $globalY, $blink, $globalDiffRound, $globalDiffLegend);
    $this->chartLocalEstimation($x, $y, $localX, $localY, $localDiffRound, $localDiffLegend);
    $this->addJavascript($globalColor, $globalRed, $globalGreen, $globalX, $globalY, $globalSlope, $globalDiff, $localX, $localY, $localSlope, $localDiff);
  }
  
  private function chartGlobalEstimation($globalColor, $globalX, $globalY, $blink, $globalDiffRound, $globalDiffLegend)
  {
      $gridOptions = array('fill' => '#'.$globalColor, 'stroke' => '#'.$globalColor, 'transform' => 'translate('._GMARGIN.','._GMARGIN.')');
      $globalEstimateGrid = $this->openGridTag('chart-global', $gridOptions);
      $lineOptions = array( 'stroke-dasharray' => '50,10,10,10', 'stroke-width' => '2');
      $globalEstimateGrid .= $this->getLineTag(0, 0, $globalX, $globalY, $lineOptions);      
      $globalEstimateGrid .= '<circle cx="'.$globalX.'" cy="'.$globalY.'" r="5">'.$blink.'</circle>';
      $globalEstimateGrid .= $this->getTextTag($globalDiffRound.$globalDiffLegend, ($globalX + 12), ($globalY + 12));
      $globalEstimateGrid .= '</g>';
      $this->graphCode .= $globalEstimateGrid;
  }
  
  private function chartLocalEstimation($x, $y, $localX, $localY, $localDiffRound, $localDiffLegend, $goalProjection=0)
  {
    
    $gridOptions = array( 'transform' => 'translate('._GMARGIN.','._GMARGIN.')');
    $localEstimateGrid = $this->openGridTag('chart-local', $gridOptions);
    $lineOptions = array( 'stroke-dasharray' => '50,10,10,10', 'stroke-width' => '2');
    $localEstimateGrid .= $this->getLineTag($x, $y, $localX, $localY, $lineOptions);
    $localEstimateGrid .= $this->getCircleTag($localX, $localY, (5 * pow($goalProjection + 1, 3)));
    $localEstimateGrid .= $this->getTextTag($localDiffRound.$localDiffLegend, ($localX + 12), ($localY + 12 + pow($goalProjection + .5, 3)));
    $localEstimateGrid .= '</g>';
    
    $this->graphCode .= $localEstimateGrid;
  }
  
  private function addJavascript($globalColor, $globalRed, $globalGreen, $globalX, $globalY, $globalSlope, $globalDiff, $localX, $localY, $localSlope, $localDiff)
  {
    $dayUnit = _GWIDTH / $this->sprint_data['days'];
    $x = $this->globalArray['x'];
    $y = $this->globalArray['y'];
    $previousX = $this->globalArray['previousX'];
    $previousY = $this->globalArray['previousY'];
    $burnedHours = $this->globalArray['burnedHours'];
    
    $this->graphCode .= "<script type='text/javascript'>
      console.log(''
        +'\n GraphMargin:'+ _GMARGIN
        +'\n unitDay:'+ $dayUnit
        +'\n ---'
        +'\n x:'+ $x
        +'\n y:'+ $y;
        +'\n previousX:'+ $previousX
        +'\n previousY:'+ $previousY
        +'\n burnedPoints:'+ $burnedHours
        +'\n ---'
        +'\n globalSlope:'+ $globalSlope
        +'\n globalX:'+ $globalX
        +'\n globalY:'+ $globalY
        +'\n globalDiff:'+ $globalDiff
        +'\n ---'
        +'\n globalRed:' +  $globalRed
        +'\n globalGreen:' + $globalGreen
        +'\n globalColor:' + $globalColor
        +'\n ---'
        +'\n localSlope:'+ $localSlope
        +'\n localX:'+ $localX
        +'\n localY:'+ $localY
        +'\n localDiff:'+ $localDiff
      );
    </script>";
  }
  
  private function buildIdealLineGrid()
  {
     $idealLineGrid = $this->openGridTag('ideal');
     
     $idealLineGrid .= $this->getLineTag(0,0,_GWIDTH,_GHEIGHT);
     //ideal hours remaining
     for ($i = 0; $i <= $sprint['days']; $i++)
     {
       $circX = $i * _GWIDTH / $this->sprint_data['days'];
       $circY = $i * _GHEIGHT / $this->sprint_date['days'];
       $idealLineGrid .= $this->getCircleTag($circX, $circY, 5);
     }
     $idealLineGrid .= '</g>';
     
     return $idealLineGrid;
  }
  
  private function openGridTag($id, options=array())
  {
    $gridOpenTag = '<g id="'.$id.'" ';
    if(!empty($options))
    {
      foreach($options as $key=>$value)
      $gridOpenTag .= $key.'="'.$value.'" ';
    }
    gridOpenTag .='>';
    
    return $gridOpenTag;
  }
  
  private function getLineTag($x1coord, $y1coord, $x2coord, $y2coord, $options=array())
  {
    $lineTag = '<line ';
    $lineTag .= isset($options['id']) ? 'id="'.$options['id'].'" ' : '';
    unset($options['id']);
    $lineTag .= 'x1="'.$x1coord.'" y1="'.$y1coord.'"x2="'.$x2coord.'" y2="'.$y2coord.'" ';
    if(!empty($options))
    {
      foreach($options as $key=>$value)
      $lineTag .= $key.'="'.$value.'" ';
    }
    $lineTag .= '/>';
    
    return $lineTag;
  }
  
  private function getSetAttributeTag($options=array())
  {
    $setTag = '<set ';
    if(!empty($options))
    {
      foreach($options as $key=>$value)
      $setTag .= $key.'="'.$value.'" ';
    }
    $setTag .= '/>';
    
    return $setTag;
  }
  
  private function getCircleTag($xcoord, $ycoord, $radius, $options=array())
  {
    $circleTag = '<circle ';
    $circleTag .= isset($options['id']) ? 'id="'.$options['id'].'" ' : '';
    unset($options['id']);
    $circleTag .= 'cx="'.$xcoord.'" cy="'$ycoord.'" r="'.$radius.'" ';
    if(!empty($options))
    {
      foreach($options as $key=>$value)
      $circleTag .= $key.'="'.$value.'" ';
    }
    $circleTag .= '/>';
    
    return $circleTag;
  }
  
  private function getRectangleTag($xcoord, $ycoord, $width, $height, $options=array())
  {
    $rectTag = '<rect ';
    $rectTag .= isset($options['id']) ? 'id="'.$options['id'].'" ' : '';
    unset($options['id']);
    $rectTag .= 'x="'.$xcoord.'" y="'$ycoord.'" width="'.$width.'" height="'.$height.'" ';
    if(!empty($options))
    {
      foreach($options as $key=>$value)
      $rectTag .= $key.'="'.$value.'" ';
    }
    $rectTag .= '/>';
    
    return $rectTag;
  }
  
  private function getTextTag($text, $xcoord, $ycoord, $options=array())
  {
     $textTag = '<text ';
     $textTag .= 'x="'.$xcoord.'" y="'$ycoord.'" ';
     if(!empty($options))
     {
       foreach($options as $key=>$value)
       $circleTag .= $key.'="'.$value.'" ';
     }
     $textTag .= '>'.$text;
     $textTag .= '</text>';
     
     return $textTag;
  }
  
  private function adjustDataByView($burndown, sprint)
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
  
  private function getData($view)
  {
    // get the burndown values
    $query = 'SELECT day, hours_left, points_left FROM TNC_burndown 
              WHERE projects_id='.$this->project_id.' AND sprint_id='.$this->sprint_id.'
              ORDER BY day';

    $dbMgr = new DatabaseManager('dev');
    $result = $dbMgr->executeQuery($query);

    $burndown_data = array();
    while( $ret = mysql_fetch_array($result, MYSQL_ASSOC) )
    {
        $this->burndown_data[] = $ret;
    }

    // get the sprint info
    $query = 'SELECT s.*, p.name FROM TNC_sprint s, TNC_projects p 
              WHERE s.projects_id='.$this->project_id.' AND s.sprint_id='.$this->sprint_id.' AND p.id=s.projects_id';

    $sprint_data = $dbMgr->executeQueryWithResult($query);
    $dbMgr->closeConnection();
    
    if($view == _ADJUSTED)
    {
      list($this->burndown_data, $this->sprint_data) = $this->adjustDataByView($burndown_data, $sprint_data);
    }
    else
    {
      $this->burndown_data = $burndown_data;
      $this->sprint_data = $sprint_data;
    }
  }
  
  private function digitNumber($value) {
    return strlen($value) < 2 ? '0'.$value : $value;
  }
}