<?php
class GraphManager
{
  private $graphHeight;
  private $graphWidth;
  private $graphMargin = 150;
  
  private $yPlotPoint;
  private $previousYPlotPoint;
  private $xPlotPoint;
  private $previousXPlotPoint;
  
  public function __construct($height, $width)
  {
    $this->graphHeight = $height;
    $this->graphWidth  = $width;
  }
  
  public function getViewBoxDimensions()
  {
    $width = $this->graphWidth + 2 * $this->graphMargin;
    $height =  $this->graphHeight + 2 * $this->graphMargin;
    $viewBoxDimensions = array($width, $height);
    
    return $viewBoxDimensions;
  }
  
  public function getGraphDimensions()
  {
    $graphDimensions = array($this->graphWidth, $this->graphHeight, $this->graphMargin);
    return $graphDimensions;
  }
  
  public function getUnitPoints($heightBase, $widthBase)
  {
    $horizontalUnits = $this->graphHeight/$heightBase;
    $verticalUnits = $this->graphWidth/$widthBase;
    
    return array($horizontalUnits, $verticalUnits);
  }
  
  public function getPlotPoints($dataArray, $plotField, $pointZeroValue, $heightBase, $widthBase, $isGlobalEstimateBase=false)
  {
    $arrayCoords = array();
    
    $count = 0;
    list($horizontalUnit, $verticalUnit) = $this->getUnitPoints($heightBase, $widthBase);
    foreach($dataArray as $data_point)
    {
      $previousX      = ($count - 1) * $horizontalUnit + $horizontalUnit;
      $x              = $i * $horizontalUnit + $horizontalUnit;

      //Y is not the same as original one
      $previousY      = (($count == 0) ? $pointZeroValue : $y ) * $verticalUnit;
      $y              = $data_point[$plotField] * $verticalUnit;
      
      $arrayCoords[$data_point[$plotField]] =  array($x, $y);
    }
    
    if($isGlobalEstimateBase)
    {
      $this->yPlotPoint  = $y;
      $this->previousYPlotPoint = $previousY;
      $this->xPlotPoint = $x;
      $this->previousXPlotPoint = $previousX;
    }
    
    return $arrayCoords;
  }
  
  public function getGlobalEstimates()
  {
      /* global estimation */
      $globalSlope      = $this->yPlotPoint/$this->xPlotPoint;
      $globalYIntercept = $this->yPlotPoint - $this->xPlotPoint * $globalSlope;
      $globalX          = $this->graphWidth;
      $globalY          = $globalX * $globalSlope + $globalYIntercept;
      $globalDiff       = $globalY * 100 / $this->graphHeight - 100;

      if($globalY > $this->graphHeight) {
        $globalY    = $this->graphHeight;
        $globalX    = $globalY / $globalSlope - $globalYIntercept;
        $globalDiff = 100 - $globalX * 100 / $this->graphWidth;
      }

      $globalDiffAbs = abs(round($globalDiff));

      $globalRed    = 100 - $globalDiffAbs == 0 ? '00' : 'ff';
      $globalGreen  = 100 - $globalDiffAbs == 0 ? 187 : min( (100 - $globalDiffAbs)*255, 210) - 75;
      $globalGreen  = $globalGreen < 0 ? '00' : sprintf('%02d', dechex($globalGreen));
      $globalColor  = $globalRed.$globalGreen.'00';

      /* local estimation */
      $localSlope       = ($this->yPlotPoint - $this->previousYPlotPoint) / ($this->xPlotPoint - $this->previousXPlotPoint);
      $localYIntercept  = $this->yPlotPoint - $this->xPlotPoint * $localSlope;
      $localX           = $this->graphWidth;
      $localY           = $localX * $localSlope + $localYIntercept;
      $localDiff        = $localY * 100 / $this->graphHeight - 100;

      if($localY > $this->graphHeight) {
        $localY     = $this->graphHeight;
        $localX     = ($localY - $localYIntercept) / $localSlope;
        $localDiff  = 100 - $localX * 100 / $this->graphWidth;
      }

      /* points left */
      $globalDiffRound = abs(round($globalDiff));
      $localDiffRound = abs(round($localDiff));

      if($globalDiffRound == 0){
        $globalDiffLegend = $localDiffLegend = 'Success!';
      }
      else{
        $globalDiffLegend = $globalDiff < 0 ? '% left' : '% more';
        $localDiffLegend  = $localDiff < 0 ? '% left' : '% more';
        $globalDiffLegend = $globalDiffRound.$globalDiffLegend;
        $localDiffLegend = $localDiffRound.$localDiffLegend;
      }
      
      $globalValues = array('slope'   => $globalSlope, 
                            'xCoord'  => $globalX,
                            'yCoord'  => $globalY, 
                            'diff'    => $globalDiff,
                            'diffAbs' => $globalDiffAbs,
                            'red'     => $globalRed,
                            'green'   => $globalGreen,
                            'color'   => $globalColor);
                            
      $localValues = array('slope'  => $localSlope,
                           'xCoord' => $localX,
                           'yCoord' => $localY,
                           'diff'   => $localDiff);
                           
      return array($globalValues, $localValues);
  }
  
  public function getLastCoords()
  {
    return array('xCoord' => $this->xPlotPoint,  'prevXCoord' => $this->previousXPlotPoint, 'yCoord' => $this->vPlotPoint, 'prevYCoord' => $this->previousXPlotPoint);
  }
}