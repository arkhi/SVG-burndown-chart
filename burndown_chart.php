<?php
/* The 3 following lines should be commented when the files is direclty embedded in a HTML5 document.
   On the contrary, the 3 following lines shouldn't be commented if this php file is used as standalone
   Just add [starSlash] caracters at the end of this line for standalone use. */
  header("Content-type: image/svg+xml");
  echo '<?xml version="1.0" encoding="utf-8"?>
  <?xml-stylesheet href="common.css" type="text/css"?>';
/**/

  /*
   * $variation can be used to experiment and test through the URL.
   */
  $variation = isset($_GET['var']) ? $_GET['var'] : 12;

  /*
   * Some data you'd like to change and update daily (dailyPoints & dailyUSPoints)
   * numbers in parentheses are added hours to the sprint
   * 20120517: #17000; Sam and Sylvain already finished 4 hours on day 0
   *
   */
  $sprint = array(
    'number'        => 34,
    'days'          => 15,
    'points'        => 10+61+22+6+77+2+6+4+20+30,
    'USPoints'      => 8+13+3+3+20+1+3+1+13+8,

    /*
     * 2012
     *
     */
    'dailyPoints'   => array(
                              1+3+5+1,
                              1+5+2,
                              6+6+6+6
                            ),
    'dailyUSPoints' => array(
                              0,
                              1,
                              3+5
                            ),
    /*
     * 2012
     *
     */
    'dailyBugs' => array(
                          1+2+3,
                          5+5+5,
                          2
                        )
  );

  $graphWidth     = 2000;
  $graphHeight    = 1000;
  $GraphMargin    = 150;
  $unitPoint      = $graphHeight / $sprint['points'];
  $unitDay        = $graphWidth / $sprint['days'];
  $coordsModifier = $sprint['points'] - $sprint['USPoints'];  /* to adapt the number of US points to the scale of tasks points */
  $bugsModifier   = 2;                                        /* so that the chart is not too tiny compared to the rest */

  $arrayTasksCoords = array();
  $arrayUSCoords = array();
?>

<svg  id="svgBurndownChart"
      version="1.1"
      xmlns="http://www.w3.org/2000/svg"
      xmlns:xlink="http://www.w3.org/1999/xlink"
      preserveAspectRatio="xMidYMid meet"
      viewBox="0 0 <?= $graphWidth + 2 * $GraphMargin; ?> <?= $graphHeight + 2 * $GraphMargin; ?>">

  <title>burndown chart - sprint <?= $sprint['number']; ?></title>

  <defs>
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
  </defs>


  <g id="grid" transform="translate(<?= $GraphMargin. ',' .$GraphMargin; ?>)">
    <rect id="graphFrame" x="0" y="0" width="<?= $graphWidth; ?>" height="<?= $graphHeight; ?>" />

    <g id="ordinate">
  <?php for ($i = 0; $i < $sprint['points']; $i += 10) : /* horizontal lines: points */ ?>
      <line x1="0" y1="<?= $graphHeight - $i * $unitPoint; ?>" x2="<?= $graphWidth; ?>" y2="<?= $graphHeight - $i * $unitPoint; ?>" />
      <text x="-12" y="<?= $graphHeight - $i * $unitPoint; ?>"><?= $i; ?></text>
  <? endfor; ?>
      <text id="sprintTasksPoints" x="-12" y="-12"><?= $sprint['points']; ?></text>
    </g><!-- #ordinate -->

    <g id="abscissa">
<?php for ($i = 0; $i < $sprint['days']; $i += 1) : /* vertical lines: days */ ?>
      <line x1="<?= $i * $unitDay; ?>" y1="0" x2="<?= $i * $unitDay; ?>" y2="<?= $graphHeight; ?>" />
  <?php if($i != 0) : ?>
      <text x="<?= $i * $unitDay; ?>" y="-12">day <?= $i; ?></text>
  <? endif; ?>
<? endfor; ?>
      <text x="<?= $graphWidth; ?>" y="-12">day <?= $sprint['days']; ?></text>
    </g><!-- #abscissa -->

    <g id="ideal">
      <line id="ideal_line" x1="0" y1="0" x2="<?= $graphWidth; ?>" y2="<?= $graphHeight; ?>" />
      <?php for ($i = 0; $i <= $sprint['days']; $i++) : /* ideal tasks remaining: dots */ ?>
      <circle cx="<?= $i * $unitDay; ?>" cy="<?= $i * $graphHeight / $sprint['days']; ?>" r="5" />
      <? endfor; ?>
    </g><!-- #ideal -->
  </g><!-- #grid -->

  <!-- That's our goal -->
  <circle id="sprintGoal" cx="<?= $graphWidth; ?>" cy="<?= $graphHeight; ?>" r="5" transform="translate(<?= $GraphMargin. ',' .$GraphMargin; ?>)" />



  <g id="legends" transform="translate(<?= $GraphMargin. ',' .$GraphMargin; ?>)">
    <text x="<?= $graphWidth - 12; ?>" y="35">Sprint <?= $sprint['number']; ?></text>
    <text class="tasksPoints" x="<?= $graphWidth - 12; ?>" y="<?= 70; ?>">Tasks: <?= $sprint['points']; ?></text>
    <text class="USPoints" x="<?= $graphWidth - 12; ?>" y="<?= 105; ?>">User Story: <?= $sprint['USPoints'] ?></text>
    <text class="bugPoints" x="<?= $graphWidth - 12; ?>" y="<?= 140; ?>">Bug Fixing Time: <?= array_sum($sprint['dailyBugs']) ?></text>
  </g><!-- /#legends -->



  <g id="chart-us" transform="translate(<?= $GraphMargin. ',' .$GraphMargin; ?>)">
<?php for ($i = 0, $burnedPoints = 0; $i < count($sprint['dailyUSPoints']); $i++) : /* user stories */ ?>
  <?php
    $previousX      = ($i-1) * $unitDay + $unitDay;
    $previousY      = ($burnedPoints + $coordsModifier) * $unitPoint;

    $burnedPoints   += $sprint['dailyUSPoints'][$i];
    $xUS            = $i * $unitDay + $unitDay;
    $yUS            = ($burnedPoints + $coordsModifier) * $unitPoint;
    $arrayUSCoords[$i] = $xUS .','. $yUS;
  ?>
    <text x="<?= $xUS + 12; ?>" y="<?= $yUS - 12; ?>"><?= $sprint['USPoints'] - $burnedPoints; ?></text>
<? endfor; ?>
    <?php if(count($arrayUSCoords) == 0){$arrayUSCoords[0] = 0;} // If array is empty, set value to 0 ?>

    <polyline points="0,<?= $coordsModifier * $unitPoint; ?> <?= implode(' ', $arrayUSCoords); ?>"
              marker-start="url(#markerUS)"
              marker-mid="url(#markerUS)"
              marker-end="url(#markerUS)" />

    <text x="12" y="<?= $coordsModifier * $unitPoint -12; ?>"><?= $sprint['USPoints']; ?></text>
  </g><!-- /#chart-us -->



  <g id="chart-tasks" transform="translate(<?= $GraphMargin. ',' .$GraphMargin; ?>)">

<?php for ($i = 0, $burnedPoints = 0; $i < count($sprint['dailyPoints']); $i++) : /* tasks */ ?>
  <?php
    $previousX    = ($i-1) * $unitDay + $unitDay;
    $previousY    = $burnedPoints * $unitPoint;

    $burnedPoints += $sprint['dailyPoints'][$i];
    $xTasks       = $i * $unitDay + $unitDay;
    $yTasks       = $burnedPoints * $unitPoint;
    $arrayTasksCoords[$i] = $xTasks .','. $yTasks;
  ?>
    <text x="<?= $xTasks - 12; ?>" y="<?= $yTasks + 12; ?>"><?= $sprint['points'] - $burnedPoints; ?></text>
<? endfor; ?>
    <?php if(count($arrayTasksCoords) == 0){$arrayTasksCoords[0] = 0;} // If array is empty, set value to 0 ?>

    <polyline points="0,0 <?= implode(' ', $arrayTasksCoords); ?>"
              marker-start="url(#markerTasks)"
              marker-mid="url(#markerTasks)"
              marker-end="url(#markerTasks)" />
  </g><!-- /#chart-tasks -->



  <g id="chart-bugs" transform="translate(<?= $GraphMargin. ',' .$GraphMargin; ?>)">

<?php for ($i = 0, $burnedPoints = 0; $i < count($sprint['dailyBugs']); $i++) : /* tasks */ ?>
  <?php
    $dailyBugs  = $sprint['dailyBugs'][$i];
    $xBugs      = $i * $unitDay + $unitDay;
    $yBugs      = $graphHeight - $dailyBugs * $unitPoint * $bugsModifier;
    $arrayBugsCoords[$i] = $xBugs .', '. $yBugs;
  ?>
    <text x="<?= $xBugs + 12; ?>" y="<?= $yBugs + 12; ?>"><?= $sprint['dailyBugs'][$i]; ?></text>
<? endfor; ?>
    <?php if(count($arrayBugsCoords) == 0){$arrayBugsCoords[0] = 0;} // If array is empty, set value to 0 ?>

    <polyline points="0,<?= $graphHeight; ?> <?= implode(' ', $arrayBugsCoords); ?>"
              marker-start="url(#markerBugs)"
              marker-mid="url(#markerBugs)"
              marker-end="url(#markerBugs)" />
  </g><!-- /#chart-bugs -->



<?php
  /* add a 0 if a value is only one digit (1 -> 01) */
  function digitNumber($value) {
    return strlen($value) < 2 ? '0'.$value : $value;
  }

  /* global estimation */
  $globalSlope      = $yTasks/$xTasks;
  $globalYIntercept = $yTasks - $xTasks * $globalSlope;
  $globalX          = $graphWidth;
  $globalY          = $globalX * $globalSlope + $globalYIntercept;
  $globalDiff       = $globalY * 100 / $graphHeight - 100;

  if($globalY > $graphHeight) {
    $globalY    = $graphHeight;
    $globalX    = $globalY / $globalSlope - $globalYIntercept;
    $globalDiff = 100 - $globalX * 100 / $graphWidth;
  }

  $globalDiffAbs = abs(round($globalDiff));

  $globalRed    = 100 - $globalDiffAbs == 0 ? '00' : 'ff';
  $globalGreen  = 100 - $globalDiffAbs == 0 ? 187 : min( (100 - $globalDiffAbs)*255, 210) - 75;
  $globalGreen  = $globalGreen < 0 ? '00' : digitNumber(dechex($globalGreen));
  $globalColor  = $globalRed.$globalGreen.'00';

  /* local estimation */
  $localSlope       = ($yTasks - $previousY) / ($xTasks - $previousX);
  $localYIntercept  = $yTasks - $xTasks * $localSlope;
  $localX           = $graphWidth;
  $localY           = $localX * $localSlope + $localYIntercept;
  $localDiff        = $localY * 100 / $graphHeight - 100;

  if($localY > $graphHeight) {
    $localY     = $graphHeight;
    $localX     = ($localY - $localYIntercept) / $localSlope;
    $localDiff  = 100 - $localX * 100 / $graphWidth;
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

  $blink  = $globalDiffAbs == 0 ? '' : '<animate attributeName="fill" values="#'.$globalColor.';#f00;#'.$globalColor.'" dur="1" repeatCount="indefinite" />';

?>
  <g id="chart-global" class="estimation" fill="#<?= $globalColor; ?>" stroke="#<?= $globalColor; ?>" transform="translate(<?= $GraphMargin. ',' .$GraphMargin; ?>)">
    <line
      x1="0" y1="0"
      x2="<?= $globalX; ?>" y2="<?= $globalY; ?>" />
    <circle cx="<?= $globalX; ?>" cy="<?= $globalY; ?>" r="<?= 5 * pow($globalDiffAbs/100+1, 3); ?>">
      <?= $blink; ?>
    </circle>
    <text x="<?= $globalX + 12; ?>" y="<?= $globalY + 12; ?>"><?= $globalDiffLegend; ?></text>
  </g>

  <g id="chart-local" class="estimation" transform="translate(<?= $GraphMargin. ',' .$GraphMargin; ?>)">
    <line
      x1="<?= $xTasks; ?>" y1="<?= $yTasks; ?>"
      x2="<?= $localX; ?>" y2="<?= $localY; ?>" />
    <circle cx="<?= $localX; ?>" cy="<?= $localY; ?>" r="5" />
    <text x="<?= $localX + 12; ?>" y="<?= $localY + 12; ?>"><?= $localDiffLegend; ?></text>
  </g>

  <g id="labels" transform="translate(<?= $GraphMargin. ',' .$GraphMargin; ?>) translate(-15, -45)">
<?php for ($i = 0; $i < count($sprint['dailyPoints']); $i++) : /* labels */ ?>
    <g id="labelGroup_<?= $i+1; ?>">
      <g id="labelTask_<?= $i+1; ?>" class="label"
         transform="translate(<?= $arrayTasksCoords[$i]; ?>)">
        <path class="labelSilhouette"
              d="M 2.96875,0.5 C 1.608085,0.5 0.5,1.608103 0.5,2.96875 L 0.5,45 c 0,8.00813 6.491872,14.5 14.5,14.5 8.008129,0 14.5,-6.49187 14.5,-14.5 l 0,-42.03125 C 29.5,1.605764 28.394171,0.5 27.03125,0.5 z M 15,35.5 c 5.246705,0 9.5,4.253295 9.5,9.5 0,5.246705 -4.253295,9.5 -9.5,9.5 -5.246705,0 -9.5,-4.253295 -9.5,-9.5 0,-5.246705 4.253295,-9.5 9.5,-9.5 z" />
        <text class="points" x="15" y="7" fill="#333" dominant-baseline="hanging" text-anchor="middle" font-size="12px"><?= $sprint['dailyPoints'][$i]; ?></text>
      </g>
      <g id="labelUS_<?= $i+1; ?>" class="label"
         transform="translate(<?= $arrayUSCoords[$i]; ?>)">
        <path class="labelSilhouette"
              d="M 2.96875,0.5 C 1.608085,0.5 0.5,1.608103 0.5,2.96875 L 0.5,45 c 0,8.00813 6.491872,14.5 14.5,14.5 8.008129,0 14.5,-6.49187 14.5,-14.5 l 0,-42.03125 C 29.5,1.605764 28.394171,0.5 27.03125,0.5 z M 15,35.5 c 5.246705,0 9.5,4.253295 9.5,9.5 0,5.246705 -4.253295,9.5 -9.5,9.5 -5.246705,0 -9.5,-4.253295 -9.5,-9.5 0,-5.246705 4.253295,-9.5 9.5,-9.5 z" />
        <text class="points"
              x="15" y="7"
              fill="#333"><?= $sprint['dailyUSPoints'][$i]; ?></text>
      </g>
    </g>
<? endfor; ?>
  </g>

  <script type="text/javascript">
  <![CDATA[
    var label = {
      burndownChart : document.getElementById('svgBurndownChart'),
      grid          : document.getElementById('grid'),
      numItemsTasks : document.getElementById('chart-tasks').getElementsByTagName('polyline')[0].points.numberOfItems,
      numItemsUS    : document.getElementById('chart-us').getElementsByTagName('polyline')[0].points.numberOfItems,
      xlinkns       : 'http://www.w3.org/1999/xlink',
      svgns         : 'http://www.w3.org/2000/svg',

      init: function(){
        this.formatGraph();
        this.drawListeners();
      },

      /*
       * Allow easier creation of SVG elements in the DOM
       * thanks to Andrew Clover for
       * http://stackoverflow.com/questions/3642035/jquerys-append-not-working-with-svg-element/3642265#3642265
       *
       */
      makeSVG: function (ns, tag, attrs) {
        var el= document.createElementNS(ns, tag);
        for (var k in attrs)
            el.setAttribute(k, attrs[k]);
        return el;
      },

      /*
       * Hide elements that can be hidden if JS is available
       * Add the proper parameters for the interactions we want
       *
       */
      formatGraph: function(){
        var labelGroups     = document.getElementById('labels').childNodes,
            labelGroupsNum  = labelGroups.length,
            fadeIn          = new Array(),
            fadeOut         = new Array(),
            labelGroupI     = 1;

        /* hide labels and define what triggers interaction */
        for(var i = 0; i < labelGroupsNum ; i++){
          if(labelGroups[i].nodeName.toLowerCase() == 'g'){
            var fadeIn = this.makeSVG(this. svgns, 'animate', {
              'attributeName' : 'opacity',
              'to'            : 1,
              'dur'           : '0.25s',
              'begin'         : 'listener_'+ labelGroupI +'.mouseover',
              'fill'          : 'freeze'
            });
            var fadeOut = this.makeSVG(this. svgns, 'animate', {
              'attributeName' : 'opacity',
              'to'            : 0,
              'dur'           : '0.25s',
              'begin'         : 'listener_'+ labelGroupI +'.mouseout',
              'fill'          : 'freeze'
            });

            labelGroups[i].setAttribute('opacity', 0);
            labelGroups[i].appendChild(fadeIn);
            labelGroups[i].appendChild(fadeOut);

            labelGroupI++;
          }
        }
      },

      /*
       * Draw listeners if JS is available
       *
       */
      drawListeners: function(){
        var abscissas     = document.getElementById('abscissa').getElementsByTagName('line');
        var abscissasNum  = abscissas.length;
        var listener = new Array();

        for(var i = 0; i < this.numItemsTasks ; i++) {
          var abscissaXValue = abscissas[i].x1.baseVal.value;
          var abscissaYValue = abscissas[i].y2.baseVal.value;

          listener[i] = this.makeSVG(this.svgns, 'rect', {
            'id'              : 'listener_'+ i,
            'class'           : 'listener',
            'x'               : abscissaXValue,
            'y'               : 0,
            'width'           : 50,
            'height'          : abscissaYValue,
            'fill'            : 'none',
            'transform'       : 'translate(-25,0)',
            'pointer-events'  : 'all',
          });

          this.grid.appendChild(listener[i]);
        }
      }
    }

    label.init();
  ]]>
  </script>

  <script type="text/javascript">
  <![CDATA[
    console.log(''
      +'\n GraphMargin: '+ <?= $GraphMargin; ?>
      +'\n unitDay: '+ <?= $unitDay; ?>
      +'\n ---'
      +'\n x: '+ <?= $x; ?>
      +'\n y: '+ <?= $y; ?>
      +'\n previousX: '+ <?= $previousX; ?>
      +'\n previousY: '+ <?= $previousY; ?>
      +'\n burnedPoints: '+ <?= $burnedPoints; ?>
      +'\n ---'
      +'\n globalSlope: '+ <?= $globalSlope; ?>
      +'\n globalX: '+ <?= $globalX; ?>
      +'\n globalY: '+ <?= $globalY; ?>
      +'\n globalDiff: '+ <?= $globalDiff; ?>
      +'\n globalDiffAbs: '+ <?= $globalDiffAbs; ?>
      +'\n ---'
      +'\n globalRed: <?= $globalRed; ?>'
      +'\n globalGreen: <?= $globalGreen; ?>'
      +'\n globalColor: <?= $globalColor; ?>'
      +'\n ---'
      +'\n localSlope: '+ <?= $localSlope; ?>
      +'\n localX: '+ <?= $localX; ?>
      +'\n localY: '+ <?= $localY; ?>
      +'\n localDiff: '+ <?= $localDiff; ?>
    );
  ]]>
  </script>
</svg>
