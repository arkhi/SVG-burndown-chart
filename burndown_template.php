<?php header("Content-type: image/svg+xml"); ?>
<?php echo '<?xml version="1.0" encoding="utf-8"?>' ?>
<?php echo '<?xml-stylesheet href="common.css" type="text/css"?>' ?>
<svg  id="svgBurndownChart"
      version="1.1"
      xmlns="http://www.w3.org/2000/svg"
      xmlns:xlink="http://www.w3.org/1999/xlink"
      preserveAspectRatio="xMidYMid meet"
      viewBox="0 0 <?php echo $viewBox[0].' '.$viewBox[1] ?>">

      <title>burndown chart - sprint <?php echo $sprintNumber ?></title>

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


      <g id="grid" transform="translate(<?php echo $graphMargin. ',' .$graphMargin; ?>)">
        <rect id="graphFrame" x="0" y="0" width="<?php echo $graphWidth; ?>" height="<? echo $graphHeight; ?>" />

        <g id="ordinate">
          <?php for ($i = 0; $i < $sprintTaskEstimate; $i += 10) : /* horizontal lines: points */ ?>
              <line x1="0" y1="<?php echo  $graphHeight - $i * $unitPoint; ?>" x2="<?php echo  $graphWidth; ?>" y2="<?php echo  $graphHeight - $i * $unitPoint; ?>" />
              <text x="-12" y="<?php echo  $graphHeight - $i * $unitPoint; ?>"><?php echo  $i; ?></text>
          <? endfor; ?>
          <text id="sprintTasksPoints" x="-12" y="-12"><?php echo $sprintTaskEstimate; ?></text>
        </g><!-- #ordinate -->

        <g id="abscissa">
          <?php for ($i = 0; $i < $sprintLength; $i += 1) : /* vertical lines: days */ ?>
              <line x1="<?php echo $i * $unitDay; ?>" y1="0" x2="<?php echo  $i * $unitDay; ?>" y2="<?php echo  $graphHeight; ?>" />
              <?php if($i != 0) : ?>
                  <text x="<?php echo  $i * $unitDay; ?>" y="-12">day <?php echo  $i; ?></text>
              <? endif; ?>
          <? endfor; ?>
          <text x="<?php echo  $graphWidth; ?>" y="-12">day <?php echo  $sprintLength; ?></text>
        </g><!-- #abscissa -->

        <g id="ideal">
          <line id="ideal_line" x1="0" y1="0" x2="<?php echo  $graphWidth; ?>" y2="<?php echo  $graphHeight; ?>" />
          <?php for ($i = 0; $i <= $sprintLength; $i++) : /* ideal tasks remaining: dots */ ?>
              <circle cx="<?php echo  $i * $unitDay; ?>" cy="<?php echo  $i * $graphHeight / $sprintLength; ?>" r="5" />
          <? endfor; ?>
        </g><!-- #ideal -->
      </g><!-- #grid -->

      <!-- That's our goal -->
      <circle id="sprintGoal" cx="<?php echo  $graphWidth; ?>" cy="<?php echo  $graphHeight; ?>" r="5" transform="translate(<?php echo  $graphMargin. ',' .$graphMargin; ?>)" />



      <g id="legends" transform="translate(<?php echo  $graphMargin. ',' .$graphMargin; ?>)">
        <text x="<?php echo  $graphWidth - 12; ?>" y="35">Sprint <?php echo  $sprintIdNumber; ?></text>
        <text class="tasksPoints" x="<?php echo  $graphWidth - 12; ?>" y="<?php echo  70; ?>">Tasks: <?php echo  $sprintTaskEstimate; ?></text>
        <text class="USPoints" x="<?php echo  $graphWidth - 12; ?>" y="<?php echo  105; ?>">User Story: <?php echo  $sprintPointsEstimate ?></text>
      </g><!-- /#legends -->



      <g id="chart-us" transform="translate(<?php echo  $graphMargin. ',' .$graphMargin; ?>)">
        <?php $coords = (count($burndownPointsData) == 0) ? 0 : ''; ?>
        <?php foreach ($burndownPointsData as $dataKey => $dataCoords) : /* user stories */ ?>
          <text x="<?php echo  $dataCoords[0] + 12; ?>" y="<?php echo  $dataCoords[1] - 12; ?>"><?php echo  $dataKey ?></text>
          <?php $coords .= $dataCoords[0].','.$dataCoords[1].' '; ?>
        <?php endforeach; ?>

        <polyline points="0,<?php echo $unitPoint; ?> <?php echo $coords; ?>"
                  marker-start="url(#markerUS)"
                  marker-mid="url(#markerUS)"
                  marker-end="url(#markerUS)" />

        <text x="12" y="<?php echo  $graphHeight/$sprintTaskEstimate - 12; ?>"><?php echo  $sprintPointsEstimate; ?></text>
      </g><!-- /#chart-us -->


      <g id="chart-tasks" transform="translate(<?php echo  $graphMargin. ',' .$graphMargin; ?>)">
        <?php $coords = (count($burndownTaskData) == 0) ? 0 : ''; ?>
        <?php foreach ($burndownTaskData as $dataKey =>  $dataCoords): /* tasks */ ?>
            <text x="<?php echo  $dataCoords[0] - 12; ?>" y="<?php echo  $dataCoords[1] + 12; ?>"><?php echo  $dataKey ?></text>
            <?php $coords .= $dataCoords[0].','.$dataCoords[1].' '; ?>
        <?php endforeach; ?>
        <?php if(count($arrayTasksCoords) == 0){$arrayTasksCoords[0] = 0;} // If array is empty, set value to 0 ?>

        <polyline points="0,0 <?php echo $coords; ?>"
                marker-start="url(#markerTasks)"
                marker-mid="url(#markerTasks)"
                marker-end="url(#markerTasks)" />
      </g><!-- /#chart-tasks -->

      <g id="chart-bugs" transform="translate(<?php echo  $graphMargin. ',' .$graphMargin; ?>)">

        <?php for ($i = 0, $burnedPoints = 0; $i < count($burndownBugsUnit); $i++) : /* tasks */ ?>
            <?php
            $dailyBugs  = $burndownBugsUnit[$i];
            $xBugs      = $i * $unitDay + $unitDay;
            $yBugs      = $graphHeight - $dailyBugs * $unitPoint;
            $arrayBugsCoords[$i] = $xBugs .', '. $yBugs;
            ?>
            <text x="<?php echo  $xBugs + 12; ?>" y="<?php echo  $yBugs + 12; ?>"><?php echo  $burndownBugsUnit[$i]; ?></text>
        <? endfor; ?>
        <?php if(count($arrayBugsCoords) == 0){$arrayBugsCoords[0] = 0;} // If array is empty, set value to 0 ?>

        <polyline points="0,<?php echo  $graphHeight; ?> <?php echo  implode(' ', $arrayBugsCoords); ?>"
                  marker-start="url(#markerBugs)"
                  marker-mid="url(#markerBugs)"
                  marker-end="url(#markerBugs)" />
      </g><!-- /#chart-bugs -->
      
      <g id="chart-global" class="estimation" fill="#<?php echo  $globalColor; ?>" stroke="#<?php echo  $globalColor; ?>" transform="translate(<?php echo  $graphMargin. ',' .$graphMargin; ?>)">
        <line x1="0" y1="0" x2="<?php echo  $globalX; ?>" y2="<?php echo  $globalY; ?>" />
        <circle cx="<?php echo  $globalX; ?>" cy="<?php echo  $globalY; ?>" r="<?php echo  5 * pow($globalDiffAbs/100+1, 3); ?>">
            <?php if($globalDiffAbs == 0):?>
              <animate attributeName="fill" values="#<?php echo $globalColor;?>;#f00;#<?php echo $globalColor;?>" dur="1" repeatCount="indefinite" />
            
            <?php endif;?>
        </circle>
        <text x="<?php echo  $globalX + 12; ?>" y="<?php echo  $globalY + 12; ?>"><?php echo  $globalDiffLegend; ?></text>
      </g>

      <g id="chart-local" class="estimation" transform="translate(<?php echo  $graphMargin. ',' .$graphMargin; ?>)">
        <line x1="<?php echo  $xTasks; ?>" y1="<?php echo  $yTasks; ?>" x2="<?php echo  $localX; ?>" y2="<?php echo  $localY; ?>" />
        <circle cx="<?php echo  $localX; ?>" cy="<?php echo  $localY; ?>" r="5" />
        <text x="<?php echo  $localX + 12; ?>" y="<?php echo  $localY + 12; ?>"><?php echo  $localDiffLegend; ?></text>
      </g>

      <g id="labels" transform="translate(<?php echo  $graphMargin. ',' .$graphMargin; ?>) translate(-15, -45)">
        <?php for ($i = 0; $i < count($burndownTasksUnit); $i++) : /* labels */ ?>
          <g id="labelGroup_<?php echo  $i+1; ?>">
              <g id="labelTask_<?php echo  $i+1; ?>" class="label" transform="translate(<?php echo  $arrayTasksCoords[$i]; ?>)">
                  <path class="labelSilhouette" d="M 2.96875,0.5 C 1.608085,0.5 0.5,1.608103 0.5,2.96875 L 0.5,45 c 0,8.00813 6.491872,14.5 14.5,14.5 8.008129,0 14.5,-6.49187 14.5,-14.5 l 0,-42.03125 C 29.5,1.605764 28.394171,0.5 27.03125,0.5 z M 15,35.5 c 5.246705,0 9.5,4.253295 9.5,9.5 0,5.246705 -4.253295,9.5 -9.5,9.5 -5.246705,0 -9.5,-4.253295 -9.5,-9.5 0,-5.246705 4.253295,-9.5 9.5,-9.5 z" />
                  <text class="points" x="15" y="7" fill="#333" dominant-baseline="hanging" text-anchor="middle" font-size="12px"><?php echo  $burndownTasksUnit[$i]; ?></text>
              </g>
              <g id="labelUS_<?php echo  $i+1; ?>" class="label" transform="translate(<?php echo  $arrayUSCoords[$i]; ?>)">
                  <path class="labelSilhouette" d="M 2.96875,0.5 C 1.608085,0.5 0.5,1.608103 0.5,2.96875 L 0.5,45 c 0,8.00813 6.491872,14.5 14.5,14.5 8.008129,0 14.5,-6.49187 14.5,-14.5 l 0,-42.03125 C 29.5,1.605764 28.394171,0.5 27.03125,0.5 z M 15,35.5 c 5.246705,0 9.5,4.253295 9.5,9.5 0,5.246705 -4.253295,9.5 -9.5,9.5 -5.246705,0 -9.5,-4.253295 -9.5,-9.5 0,-5.246705 4.253295,-9.5 9.5,-9.5 z" />
                  <text class="points" x="15" y="7" fill="#333"><?php echo  $burndownPointsUnit[$i]; ?></text>
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
      +'\n GraphMargin: '+ <?php echo  $graphMargin; ?>
      +'\n unitDay: '+ <?php echo  $unitDay; ?>
      +'\n ---'
      +'\n x: '+ <?php echo  $lastCoords['xCoord']; ?>
      +'\n y: '+ <?php echo  $lastCoords['yCoord']; ?>
      +'\n previousX: '+ <?php echo  $lastCoords['prevXCoord']; ?>
      +'\n previousY: '+ <?php echo  $lastCoords['prevYCoord']; ?>
      +'\n ---'
      +'\n globalSlope: '+ <?php echo  $globalEstimates['slope']; ?>
      +'\n globalX: '+ <?php echo  $globalEstimates['xCoord']; ?>
      +'\n globalY: '+ <?php echo  $globalEstimates['yCoord']; ?>
      +'\n globalDiff: '+ <?php echo  $globalEstimates['diff']; ?>
      +'\n globalDiffAbs: '+ <?php echo  $globalEstimates['diffAbs']; ?>
      +'\n ---'
      +'\n globalRed: <?php echo  $globalEstimates['red']; ?>'
      +'\n globalGreen: <?php echo  $globalEstimates['green']; ?>'
      +'\n globalColor: <?php echo  $globalEstimates['color']; ?>'
      +'\n ---'
      +'\n localSlope: '+ <?php echo  $localEstimates['slope']; ?>
      +'\n localX: '+ <?php echo  $localEstimates['xCoord']; ?>
      +'\n localY: '+ <?php echo  $localEstimates['yCoord']; ?>
      +'\n localDiff: '+ <?php echo  $localEstimates['diff']; ?>
    );
  ]]>
  </script>
</svg>