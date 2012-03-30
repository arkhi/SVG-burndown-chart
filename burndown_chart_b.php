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
    $this->addGraphTitle();
    $this->addGraphDefinitions();
    $this->addLegendsGrid();
    $this->chartStoryPoints();
    $this->chartTaskHours();
    $this->calculateEstimations();
    $this->graphCode .= '</svg>';
    
  }
  
  private function initializeGraph()
  {
    $width = self::_GWIDTH + 2 * self::_GMARGIN;
    $height = self::_GHEIGHT + 2 * self::_GMARGIN;
    $this->graphCode .= '<svg  id="svgBurndownChart" version="1.1" 
                              xmlns="http://www.w3.org/2000/svg" 
                              xmlns:xlink="http://www.w3.org/1999/xlink"
                              preserveAspectRatio="xMidYMid meet"
                              viewBox="0 0'.$width.' '.$height.'"';
  }
  
  private function addGraphTitle()
  {
    $this->graphCode .= '<title>burndown chart - sprint '.$this->sprint_data['sprint_id'].'</title>';
  }
  
  //NOTE: let the $view parameter decide which markers to use
  private function addGraphDefinitions()
  {
    $this->graphCode .= '<defs>
      <!-- filters -->
      <filter id="dropShadow">
        <feGaussianBlur in="SourceGraphic" stdDeviation="1" />
      </filter>
       <marker id="markerTasks" class="markerNode"
          viewBox="-7 -7 14 14"
          markerWidth="4" markerHeight="4"
          orient="auto">
          <circle cx="0" cy="0" r="5" />
        </marker>
        <marker id="markerUS" class="markerNode"
          viewBox="-7 -7 14 14"
          markerWidth="5" markerHeight="5"
          orient="auto">
          <circle cx="0" cy="0" r="5" />
        </marker>
        <marker id="markerBugs"
          viewBox="0 0 14 14"
          refX="7" refY="7"
          markerWidth="5" markerHeight="5"
          orient="auto">
          <circle cx="7" cy="7" r="5" fill="#fff" stroke="#090" stroke-width="2" />
        </marker>
    </defs>';
  }
  
  private function addBaseGraphGrid()
  {
    $gridOptions = array( 'transform' => 'translate('.self::_GMARGIN.','.self::_GMARGIN.')');
    $baseGrid = $this->openGridTag('grid', $gridOptions);
    
    $rectOptions = array( 'id' => 'graphFrame');
    $baseGrid .= $this->getRectangleTag(0, 0, self::_GWIDTH, self::_GHEIGHT, $rectOptions);
    
    // Create the Ordinate Grid 
    $ordinateGrid = $this->openGridTag('ordinate');
    for($i = 0; $i < $this->sprint_data['hours_estimate']; $i += 10)
    {
      $x1Coord = 0; 
      $yCoord = self::_GHEIGHT - $i * (self::_GHEIGHT / $this->sprint_data['hours_estimate']);
      $x2Coord = self::_GWIDTH;
    
      $lineTag = $this->getLineTag($x1Coord, $yCoord, $x2Coord, $yCoord);
      
      $x1Coord = -12;
      $textTag = $this->getTextTag($i, $x1Coord, $yCoord);
      $ordinateGrid .= $lineTag.$textTag;
    }
    $textOptions = array('id' => 'sprintTasksPoints');
    $x1Coord = -12; 
    $yCoord = -12;
    $textTag2 = $this->getTextTag($this->sprint_data['hours_estimate'], $x1Coord, $yCoord, $textOptions);
    $ordinateGrid .= $textTag2.'</g><!-- #ordinate -->';
    
    // Create the Abscissa Grid
    $abscissaGrid = $this->openGridTag('abscissa');
    for($i = 0; $i < $this->sprint_data['length']; $i += 10)
    {
      $xCoord = self::_GWIDTH - $i * (self::_GWIDTH / $this->sprint_data['length']); 
      $y2Coord = self::_GHEIGHT;
      $y1Coord = 0;
    
      $lineTag = $this->getLineTag($xCoord, $y1Coord, $xCoord, $y2Coord);
      $textTag = '';
      
      if($i != 0)
      {
          $text = 'day '.$i;
          $yCoord = -12;
          $xCoord = $i * (self::_GWIDTH / $this->sprint_data['length']);
          $textTag = $this->getTextTag($text, $xCoord, $yCoord);
      }
      
      $abscissaGrid .= $lineTag.$textTag;

    }
    $text = 'day '.$sprint['length'];
    $x1Coord = self::_GWIDTH; 
    $yCoord = -12;
    $textTag2 = $this->getTextTag($text, $x1Coord, $yCoord);
    $abscissaGrid .= $textTag2.'</g><!-- #abscissa -->';
    
    //Create the Ideal line grid
    $idealLineGrid = $this->buildIdealLineGrid(); 
    
    //Finish the base grid by concatenating the ordinate, abscissa and ideal line grids
    $baseGrid .= $ordinateGrid.$abscissaGrid.$idealLineGrid.'</g>';
    
    $this->graphCode .= $baseGrid;
  }
  
  private function addGoalPoint()
  {  
    $circOptions = array('id'=>'sprintGaol', 'transform' => 'translate('.self::_GMARGIN.', '.self::_GMARGIN.')');
    $goalPoint = $this->addCircleTag(self::_GWIDTH, self::_GHEIGHT, 5, $circOptions);
    
    $this->graphCode .= $goalPoint;
  }
  
  private function addLegendsGrid()
  {
     $gridOptions = array( 'transform' => 'translate('.self::_GMARGIN.','.self::_GMARGIN.')');
     $legendsGrid = $this->openGridTag('legends', $gridOptions);
     
     $xCoord = self::_GWIDTH - 12;
     $yCoord = 35;
     $text = 'Sprint '.$this->sprint_data['sprint_id'];
     $sprintText = $this->getTextTag($text, $xCoord, $yCoord);
     
     $yCoord = 70;
     $text = 'Tasks: '.$this->sprint_data['hours_estimate'];
     $textOptions = array('class' => 'tasksPoints');
     $hoursText = $this->getTextTag($text, $xCoord, $yCoord, $textOptions);
     
     $yCoord = 105;
     $text = 'User Story: '.$this->sprint_data['points_estimate'];
     $textOptions = array('class' => 'tasksPoints');
     $storyText = $this->getTextTag($text, $xCoord, $yCoord, $textOptions);
     
     $this->graphCode .= $legendsGrid.$sprintText.$hoursText.$storyText.'</g><!-- #legends -->';

  }
  
  private function chartStoryPoints()
  {
    $gridOptions = array( 'transform' => 'translate('.self::_GMARGIN.','.self::_GMARGIN.')');
    $storyPointGrid = $this->openGridTag('chart-us', $gridOptions);
    $arrayUSCoords = array();
    $dayUnit = self::_GWIDTH / $this->sprint_data['length'];
    $pointsUnit = self::_GHEIGHT / $this->sprint_data['hours_estimate'];
    $i = 0;

    foreach($this->burndown_data as $data_point)
    {
      $previousX      = ($i-1) * $dayUnit + $dayUnit;
      $x              = $i * $dayUnit + $dayUnit;
      
      //Y is not the same as original one
      $previousY      = (($i == 0) ? $this->sprint_data['points_estimate'] : $y ) * $pointsUnit;
      $y              = $data_point['points_left'] * $pointsUnit;
      
      $storyPointGrid .= $this->getTextTag( $data_point['points_left'], $x+12, $y-12);
      $arrayUSCoords[] = $x.','.$y;
    }
    $storyPointGrid .= $this->getPolyLineTag(array('start'=>'markerUS', 'mid'=>'markerUS', 'end'=>'markerUS'), 
                                $pointsUnit, $arrayUSCoords);
    
    $storyPointGrid .= $this->getTextTag( $data_point['point_estimate'], 12, $pointsUnit-12);
    $storyPointGrid .= "</g><!-- /#chart-us -->";
    
    $this->graphCode .= $storyPointGrid;
  }
  


############################WE ARE HERE##################




  
  private function chartTaskHours()
  {
    
    $gridOptions = array( 'transform' => 'translate('.self::_GMARGIN.','.self::_GMARGIN.')');
    $taskHoursGrid = $this->openGridTag('chart-tasks', $gridOptions);
    $dayUnit = self::_GWIDTH / $this->sprint_data['length'];
    $pointsUnit = self::_GHEIGHT / $this->sprint_data['hours_estimate'];

    $i = 0;
    foreach($this->burndown_data as $data_point)
    {
      $previousX      = ($i-1) * $dayUnit + $dayUnit;
      $x              = $i * $dayUnit + $dayUnit;
      
      $previousY      = (($i == 0) ? $this->sprint_data['hours_estimate'] : $y ) * $pointsUnit;
      $y              = $data_point['hours_left'] * $pointsUnit;
      $lineOptions = array('id' => 'day'.$i);
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
      
      $beginParam = 'day'.$i.'.mouseover;point'.($i-1).'.mouseover';
      $setOptions = array('attributeName' => 'opacity', 'to' => '1', 'begin' => $beginParam);
      $textAttribute =  $this->getSetAttributeTag($setOptions);
      
      $beginParam = 'day'.$i.'.mouseout;point'.($i-1).'.mouseout';
      $setOptions = array('attributeName' => 'opacity', 'to' => '0', 'begin' => $beginParam);
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
    $globalX          = self::_GWIDTH;
    $globalY          = $globalX * $globalSlope + $globalYIntercept;
    $globalDiff       = $globalY * 100 / self::_GHEIGHT - 100;

    if($globalY > self::_GHEIGHT) {
      $globalY    = self::_GHEIGHT;
      $globalX    = $globalY / $globalSlope - $globalYIntercept;
      $globalDiff = 100 - $globalX * 100 / self::_GWIDTH;
    }

    $globalRed    = abs(round(100 - $globalDiff)) == 0 ? '00' : 'ff';
    $globalGreen  = abs(round(100 - $globalDiff)) == 0 ? 187 : min( (1)*255, 210) - 75;
    $globalGreen  = $globalGreen < 0 ? '00' : $this->digitNumber(dechex($globalGreen));
    $globalColor  = $globalRed.$globalGreen.'00';

    /* local estimation */
    $localSlope       = ($y - $previousY) / ($x - $previousX);
    $localYIntercept  = $y - $x * $localSlope;
    $localX           = self::_GWIDTH;
    $localY           = $localSlope * $localX + $localYIntercept;
    $localDiff        = $localY * 100 / self::_GHEIGHT - 100;

    if($localY > self::_GHEIGHT) {
      $localY     = self::_GHEIGHT;
      $localX     = ($localY - $localYIntercept) / $localSlope;
      $localDiff  = 100 - $localX * 100 / self::_GWIDTH;
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
  
  //markerId = array('start'=>'#markerName', 'mid'=>'#markerName', 'end'=>'#markerName')
  private function getPolyLineTag($markerId=array(), $unitPoint, $arrayUSCoords)
  {
    // If array is empty, set value to 0
    if(count($arrayUSCoords) == 0)
    {
      $arrayUSCoords[0] = 0;
    }
    $polylineTag = sprintf('<polyline points="0, %s %s
               marker-start="url(%s)"
               marker-mid="url(%s)"
               marker-end="url(%s)" />', 
               $unitPoint, 
               implode(' ', $arrayUSCoords), 
               $markerId['start'], 
               $markerId['mid'], 
               $markerId['end']);
    return $polylineTag;
  }
  
  private function chartGlobalEstimation($globalColor, $globalX, $globalY, $blink, $globalDiffRound, $globalDiffLegend)
  {
      $gridOptions = array('fill' => '#'.$globalColor, 'stroke' => '#'.$globalColor, 'transform' => 'translate('.self::_GMARGIN.','.self::_GMARGIN.')');
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
    
    $gridOptions = array( 'transform' => 'translate('.self::_GMARGIN.','.self::_GMARGIN.')');
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
    $dayUnit = self::_GWIDTH / $this->sprint_data['length'];
    $x = $this->globalArray['x'];
    $y = $this->globalArray['y'];
    $previousX = $this->globalArray['previousX'];
    $previousY = $this->globalArray['previousY'];
    $burnedHours = $this->globalArray['burnedHours'];
    
    $this->graphCode .= "<script type='text/javascript'>
      console.log(''
        +'\n GraphMargin:'+ self::_GMARGIN
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
     
     $x1Coord = 0;
     $y1Coord = 0;
     $x2Coord = self::_GWIDTH;
     $y2Coord = self::_GHEIGHT;
     
     $idealLineGrid .= $this->getLineTag($x1Coord, $y1Coord, $x2Coord, $y2Coord, array('id' => 'ideal_line'));
     //ideal hours remaining
     for($i = 0; $i <= $sprint['length']; $i++)
     {
       $circX = $i * self::_GWIDTH / $this->sprint_data['length'];
       $circY = $i * self::_GHEIGHT / $this->sprint_date['length'];
       $idealLineGrid .= $this->getCircleTag($circX, $circY, 5);
     }
     $idealLineGrid .= '</g>';
     
     return $idealLineGrid;
  }
  
  private function openGridTag($id, $options=array())
  {
    $gridOpenTag = '<g id="'.$id.'" ';
    if(!empty($options))
    {
      foreach($options as $key=>$value)
      {
        $gridOpenTag .= $key.'="'.$value.'" ';
      }
    }
    $gridOpenTag .= '>';
    
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
    $circleTag .= 'cx="'.$xcoord.'" cy="'.$ycoord.'" r="'.$radius.'" ';
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
    $rectTag .= 'x="'.$xcoord.'" y="'.$ycoord.'" width="'.$width.'" height="'.$height.'" ';
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
     $textTag .= 'x="'.$xcoord.'" y="'.$ycoord.'" ';
     if(!empty($options))
     {
       foreach($options as $key=>$value)
       $circleTag .= $key.'="'.$value.'" ';
     }
     $textTag .= '>'.$text;
     $textTag .= '</text>';
     
     return $textTag;
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
  
  private function digitNumber($value) {
    return strlen($value) < 2 ? '0'.$value : $value;
  }
}