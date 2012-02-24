<?php
/* you don't want that when you embed the file direclty in the HTML
  header("Content-type: image/svg+xml");
  echo '<?xml version="1.0" encoding="utf-8"?>
  <?xml-stylesheet href="common.css" type="text/css"?>';
/**/

  $variation = isset($_GET['var']) ? $_GET['var'] : 11;

  /*
   * Some data you'd like to change and update daily (dailyPoints & dailyUSPoints)
   */
  $sprint = array(
    'number'        => 30,
    'days'          => 15,
    'points'        => 197,
    'USPoints'      => 55,
    'dailyPoints'   => array(12,21,17,$variation
                            ),
    'dailyUSPoints' => array( 0, 0, 0, 0
                            ),
    'dailyBugs'     => array( 2, 3, 2, 3
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
    <marker id="markerTasks"
      viewBox="0 0 14 14"
      refX="7" refY="7"
      markerWidth="5" markerHeight="5"
      orient="auto">
      <circle cx="7" cy="7" r="5" fill="#fff" stroke="#900" stroke-width="2" />
    </marker>
    <marker id="markerUS"
      viewBox="0 0 14 14"
      refX="7" refY="7"
      markerWidth="5" markerHeight="5"
      orient="auto">
      <circle cx="7" cy="7" r="5" fill="#fff" stroke="#069" stroke-width="2" />
    </marker>
  </defs>

  <g id="grid" transform="translate(<?= $GraphMargin. ',' .$GraphMargin; ?>)">
<?php for ($i = 0; $i < $sprint['points']; $i += 10) : /* horizontal lines: points */ ?>
    <line x1="0" y1="<?= $graphHeight - $i * $unitPoint; ?>" x2="<?= $graphWidth; ?>" y2="<?= $graphHeight - $i * $unitPoint; ?>" stroke-dasharray="5,5" />
    <text x="-12" y="<?= $graphHeight - $i * $unitPoint; ?>" text-anchor="end" dominant-baseline="middle"><?= $i; ?></text>
<? endfor; ?>

<?php for ($i = 0; $i < $sprint['days']; $i += 1) : /* vertical lines: days */ ?>
    <line x1="<?= $graphWidth - $i * $unitDay; ?>" y1="0" x2="<?= $graphWidth - $i * $unitDay; ?>" y2="<?= $graphHeight; ?>" stroke-dasharray="5,5" />
  <?php if($i != 0) : ?>
    <text x="<?= $i * $unitDay; ?>" y="-12" text-anchor="middle">day <?= $i; ?></text>
  <? endif; ?>
<? endfor; ?>

    <g id="ideal">
      <line id="ideal_line" x1="0" y1="0" x2="<?= $graphWidth; ?>" y2="<?= $graphHeight; ?>" />
      <?php for ($i = 0; $i <= $sprint['days']; $i++) : /* ideal tasks remaining: dots */ ?>
      <circle cx="<?= $i * $unitDay; ?>" cy="<?= $i * $graphHeight / $sprint['days']; ?>" r="5" />
      <? endfor; ?>
    </g><!-- #grid -->

    <!-- frame -->
    <rect x="0" y="0" width="<?= $graphWidth; ?>" height="<?= $graphHeight; ?>" fill="none" stroke="#000" />
  </g><!-- #grid -->

  <!-- That's our goal -->
  <circle cx="<?= $graphWidth; ?>" cy="<?= $graphHeight; ?>" r="5" fill="#090" stroke="#000" transform="translate(<?= $GraphMargin. ',' .$GraphMargin; ?>)" />



  <g id="legends" transform="translate(<?= $GraphMargin. ',' .$GraphMargin; ?>)">
    <text x="-12" y="-12" fill="#900" text-anchor="end"><?= $sprint['points']; ?></text>
    <text x="12" y="<?= $graphHeight - 88; ?>" fill="#999">Sprint <?= $sprint['number']; ?></text>
    <text x="12" y="<?= $graphHeight - 50; ?>" fill="#900">Tasks: <?= $sprint['points']; ?></text>
    <text x="12" y="<?= $graphHeight - 12; ?>" fill="#069">User Story: <?= $sprint['USPoints'] ?></text>
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

    <polyline fill="none" stroke="#069" stroke-width="3"
              points="0,<?= $coordsModifier * $unitPoint; ?> <?= implode(' ', $arrayUSCoords); ?>"
              marker-start="url(#markerUS)"
              marker-mid="url(#markerUS)"
              marker-end="url(#markerUS)" />

    <text x="12" y="<?= $coordsModifier * $unitPoint -12; ?>" fill="#069"><?= $sprint['USPoints']; ?></text>
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

    <polyline fill="none" stroke="#900" stroke-width="3"
              points="0,0 <?= implode(' ', $arrayTasksCoords); ?>"
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
  $localY           = $localSlope * $localX + $localYIntercept;
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
    $localDiffLegend = $globalDiffRound.$localDiffLegend;
  }

  $blink  = $globalDiffAbs == 0 ? '' : '<animate attributeName="fill" values="#'.$globalColor.';#f00;#'.$globalColor.'" dur="1" repeatCount="indefinite" />';

?>
  <g id="chart-global" fill="#<?= $globalColor; ?>" stroke="#<?= $globalColor; ?>" transform="translate(<?= $GraphMargin. ',' .$GraphMargin; ?>)">
    <line
      x1="0" y1="0"
      x2="<?= $globalX; ?>" y2="<?= $globalY; ?>"
      stroke-dasharray="50,10,10,10" stroke-width="2" />
    <circle cx="<?= $globalX; ?>" cy="<?= $globalY; ?>" r="<?= 5 * pow($globalDiffAbs/100+1, 3); ?>">
      <?= $blink; ?>
    </circle>
    <text x="<?= $globalX + 12; ?>" y="<?= $globalY + 12; ?>"><?= $globalDiffLegend; ?></text>
  </g>

  <g id="chart-local" transform="translate(<?= $GraphMargin. ',' .$GraphMargin; ?>)">
    <line
      x1="<?= $x; ?>" y1="<?= $y; ?>"
      x2="<?= $localX; ?>" y2="<?= $localY; ?>"
      stroke-dasharray="50,10,10,10" stroke-width="2" />
    <circle cx="<?= $localX; ?>" cy="<?= $localY; ?>" r="<?= 5 * pow($goalProjection+1, 3); ?>">
    </circle>
    <text x="<?= $localX + 12; ?>" y="<?= $localY + 12 + pow($goalProjection + .5, 3); ?>"><?= $localDiffLegend; ?></text>
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
