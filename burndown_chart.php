<?php
/* You don't want that when you embed the file direclty in the HTML.
   The 3 following lines should be commented when the files is direclty embedded in a HTML5 document.
   On the contrary, the 3 following lines shouldn't be commented if this php file is used as a standalone
   Just add [starSlash] caracters at the end of this line.
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
   */
  $sprint = array(
    'number'        => 30,
    'days'          => 15,
    'points'        => 197,
    'USPoints'      => 55,
    'dailyPoints'   => array(12,21,17, 6,$variation
                            ),
    'dailyUSPoints' => array( 0, 3, 1, 5, 0
                            ),
    'dailyBugs'     => array( 2, 3, 2, 3, 0
                            )
  );

  $graphWidth     = 2000;
  $graphHeight    = 1000;
  $GraphMargin    = 150;
  $unitPoint      = $graphHeight / $sprint['points'];
  $unitDay        = $graphWidth / $sprint['days'];
  $coordsModifier = $sprint['points'] - $sprint['USPoints']; /* to adapt the number of US points to the scale of tasks points */

  $arrayTasksCoords = array();
  $arrayUSCoords = array();
?>

<svg  version="1.1"
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
  </defs>

  <g id="grid" transform="translate(<?= $GraphMargin. ',' .$GraphMargin; ?>)">
    <g id="ordinate">
  <?php for ($i = 0; $i < $sprint['points']; $i += 10) : /* horizontal lines: points */ ?>
      <line x1="0" y1="<?= $graphHeight - $i * $unitPoint; ?>" x2="<?= $graphWidth; ?>" y2="<?= $graphHeight - $i * $unitPoint; ?>" />
      <text x="-12" y="<?= $graphHeight - $i * $unitPoint; ?>"><?= $i; ?></text>
  <? endfor; ?>
      <text id="sprintTasksPoints" x="-12" y="-12"><?= $sprint['points']; ?></text>
    </g><!-- #ordinate -->

    <g id="abscissa">
<?php for ($i = 0; $i < $sprint['days']; $i += 1) : /* vertical lines: days */ ?>
      <line x1="<?= $graphWidth - $i * $unitDay; ?>" y1="0" x2="<?= $graphWidth - $i * $unitDay; ?>" y2="<?= $graphHeight; ?>" />
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
    </g><!-- #grid -->

    <!-- frame -->
    <rect id="graphFrame" x="0" y="0" width="<?= $graphWidth; ?>" height="<?= $graphHeight; ?>" />
  </g><!-- #grid -->

  <!-- That's our goal -->
  <circle id="sprintGoal" cx="<?= $graphWidth; ?>" cy="<?= $graphHeight; ?>" r="5" transform="translate(<?= $GraphMargin. ',' .$GraphMargin; ?>)" />



  <g id="legends" transform="translate(<?= $GraphMargin. ',' .$GraphMargin; ?>)">
    <text x="12" y="<?= $graphHeight - 88; ?>">Sprint <?= $sprint['number']; ?></text>
    <text class="tasksPoints" x="12" y="<?= $graphHeight - 50; ?>">Tasks: <?= $sprint['points']; ?></text>
    <text class="USPoints" x="12" y="<?= $graphHeight - 12; ?>">User Story: <?= $sprint['USPoints'] ?></text>
  </g><!-- /#legends -->



  <g id="chart-us" transform="translate(<?= $GraphMargin. ',' .$GraphMargin; ?>)">
<?php for ($i = 0, $burnedPoints = 0; $i < count($sprint['dailyUSPoints']); $i++) : /* user stories */ ?>
  <?php
    $previousX      = ($i-1) * $unitDay + $unitDay;
    $previousY      = ($burnedPoints + $coordsModifier) * $unitPoint;
    $burnedPoints   += $sprint['dailyUSPoints'][$i];
    $x              = $i * $unitDay + $unitDay;
    $y              = ($burnedPoints + $coordsModifier) * $unitPoint;
    $arrayUSCoords[$i] = $x .', '. $y;
  ?>
    <text x="<?= $x + 12; ?>" y="<?= $y - 12; ?>"><?= $sprint['USPoints'] - $burnedPoints; ?></text>
<? endfor; ?>

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
    $x            = $i * $unitDay + $unitDay;
    $y            = $burnedPoints * $unitPoint;
    $arrayTasksCoords[$i] = $x .', '. $y;
  ?>
    <text x="<?= $x - 12; ?>" y="<?= $y + 12; ?>"><?= $sprint['points'] - $burnedPoints; ?></text>

    <text class="pointsBurned" x="<?= $previousX + 12; ?>" y="<?= $previousY - 12; ?>" opacity="0">
      <set attributeName="opacity" to="1" begin="day<?= $i ?>.mouseover;point<?= $i-1 ?>.mouseover" />
      <set attributeName="opacity" to="0" begin="day<?= $i ?>.mouseout;point<?= $i-1 ?>.mouseout" />
      <?= $sprint['dailyPoints'][$i]; ?>
    </text>
<? endfor; ?>

    <polyline points="0,0 <?= implode(' ', $arrayTasksCoords); ?>"
              marker-start="url(#markerTasks)"
              marker-mid="url(#markerTasks)"
              marker-end="url(#markerTasks)" />
  </g><!-- /#chart-tasks -->



<?php
  /* add a 0 if a value is only one digit (1 -> 01) */
  function digitNumber($value) {
    return strlen($value) < 2 ? '0'.$value : $value;
  }

  /* global estimation */
  $globalSlope      = $y/$x;
  $globalYIntercept = $y - $x * $globalSlope;
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
  $localSlope       = ($y - $previousY) / ($x - $previousX);
  $localYIntercept  = $y - $x * $localSlope;
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
      x1="<?= $x; ?>" y1="<?= $y; ?>"
      x2="<?= $localX; ?>" y2="<?= $localY; ?>" />
    <circle cx="<?= $localX; ?>" cy="<?= $localY; ?>" r="5" />
    <text x="<?= $localX + 12; ?>" y="<?= $localY + 12; ?>"><?= $localDiffLegend; ?></text>
  </g>

  <script type="text/javascript">
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
  </script>
</svg>
