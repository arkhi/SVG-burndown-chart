<?php
  /* NOTES:
   * units have been changed from percents to pixels
   */

  /* TODO
   * Sylvain: add a red arrow showing the deadline
   */

  header("Content-type: image/svg+xml");
  echo '<?xml version="1.0" encoding="utf-8"?>
  <?xml-stylesheet href="../common.css" type="text/css"?>';

  $variation = isset($_GET['var']) ? $_GET['var'] : 5;

  /*
   * Some data you'd like to change and update daily (dailyPoints & dailyUSPoints)
   */
  $sprint = array(
    'number'        => 28,
    'days'          => 20,
    'points'        => 121,
    'USPoints'      => 37,
    'dailyPoints'   => array( 9, 7, 5, 9, 2,
                              2, 1, 1, 1, 4,
                              1, $variation
                            ),
    'dailyUSPoints' => array( 0, 0, 0, 3, 0,
                              0, 0, 0, 0, 0,
                              0, 5
                            )
  );

  $graphWidth     = 2000;
  $graphHeight    = 1000;
  $GraphMargin    = 50;
  $unitPoint      = $graphHeight / $sprint['points'];
  $unitDay        = $graphWidth / $sprint['days'];
  $coordsModifier = $sprint['points'] - $sprint['USPoints']; /* to adapt the number of US points to the scale of tasks points */
?>

<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
  <title>burndown chart - sprint <?= $sprint['number']; ?></title>

  <defs>
    <!-- filters -->
    <filter id="dropShadow">
      <feGaussianBlur in="SourceGraphic" stdDeviation="1" />
    </filter>
  </defs>

  <g id="grid" transform="translate(<?= $GraphMargin. ',' .$GraphMargin; ?>)">
<?php for ($i = 0; $i < $sprint['points']; $i += 10) : /* horizontal lines: points */ ?>
    <line x1="0" y1="<?= $graphHeight - $i * $unitPoint; ?>" x2="<?= $graphWidth; ?>" y2="<?= $graphHeight - $i * $unitPoint; ?>" stroke-dasharray="5,5" />
    <text x="-.5" y="<?= $graphHeight - $i * $unitPoint; ?>" text-anchor="end" dominant-baseline="middle"><?= $i; ?></text>
<? endfor; ?>

<?php for ($i = 0; $i < $sprint['days']; $i += 1) : /* vertical lines: days */ ?>
    <line x1="<?= $graphWidth - $i * $unitDay; ?>" y1="0" x2="<?= $graphWidth - $i * $unitDay; ?>" y2="<?= $graphHeight; ?>" />
  <?php if($i != 0) : ?>
    <text x="<?= $i * $unitDay; ?>" y="<?= $graphHeight + .5; ?>" text-anchor="middle" dominant-baseline="hanging">day <?= $i; ?></text>
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
    <text x="-3" y="-.5" fill="#900">Tasks</text>
    <text x="-.5" y="-.5" fill="#900"><?= $sprint['points']; ?></text>
    <text x="-3" y="<?= $coordsModifier * $unitPoint; ?>" fill="#069">User Story</text>
  </g><!-- /#legends -->



  <g id="chart-us" transform="translate(<?= $GraphMargin. ',' .$GraphMargin; ?>)">
<?php for ($i = 0, $burnedPoints = 0; $i < count($sprint['dailyUSPoints']); $i++) : /* user stories */ ?>
  <?php
    $previousX      = ($i-1) * $unitDay + $unitDay;
    $previousY      = ($burnedPoints + $coordsModifier) * $unitPoint;
    $burnedPoints   += $sprint['dailyUSPoints'][$i];
    $x              = $i * $unitDay + $unitDay;
    $y              = ($burnedPoints + $coordsModifier) * $unitPoint;
  ?>
    <line x1="<?= $previousX; ?>" y1="<?= $previousY; ?>" x2="<?= $x; ?>" y2="<?= $y; ?>" />
<? endfor; ?>
<?php for ($i = 0, $burnedPoints = 0; $i < count($sprint['dailyUSPoints']); $i++) : /* user stories */ ?>
  <?php
    $previousX      = ($i-1) * $unitDay + $unitDay;
    $previousY      = ($burnedPoints + $coordsModifier) * $unitPoint;
    $burnedPoints   += $sprint['dailyUSPoints'][$i];
    $x              = $i * $unitDay + $unitDay;
    $y              = ($burnedPoints + $coordsModifier) * $unitPoint;
  ?>
    <circle cx="<?= $x; ?>" cy="<?= $y; ?>" r="5" />
    <text x="<?= $x + .5; ?>" y="<?= $y - .5; ?>"><?= $sprint['USPoints'] - $burnedPoints; ?></text>
<? endfor; ?>

    <circle cx="0" cy="<?= $coordsModifier * $unitPoint; ?>" r="5" />
    <text x=".5" y="<?= $coordsModifier * $unitPoint -.5; ?>" fill="#069"><?= $sprint['USPoints']; ?></text>
  </g><!-- /#chart-us -->



  <g id="chart-tasks" transform="translate(<?= $GraphMargin. ',' .$GraphMargin; ?>)">
<?php for ($i = 0, $burnedPoints = 0; $i < count($sprint['dailyPoints']); $i++) : /* tasks */ ?>
  <?php
    $previousX    = ($i-1) * $unitDay + $unitDay;
    $previousY    = $burnedPoints * $unitPoint;
    $burnedPoints += $sprint['dailyPoints'][$i];
    $x            = $i * $unitDay + $unitDay;
    $y            = $burnedPoints * $unitPoint;
  ?>
    <line id="day<?= $i ?>" x1="<?= $previousX; ?>" y1="<?= $previousY; ?>" x2="<?= $x; ?>" y2="<?= $y; ?>" />

<? endfor; ?>
<?php for ($i = 0, $burnedPoints = 0; $i < count($sprint['dailyPoints']); $i++) : /* tasks */ ?>
  <?php
    $previousX    = ($i-1) * $unitDay + $unitDay;
    $previousY    = $burnedPoints * $unitPoint;
    $burnedPoints += $sprint['dailyPoints'][$i];
    $x            = $i * $unitDay + $unitDay;
    $y            = $burnedPoints * $unitPoint;
  ?>
    <circle id="point<?= $i ?>" cx="<?= $x; ?>" cy="<?= $y; ?>" r="5" />
    <text x="<?= $x - .5; ?>" y="<?= $y + .5; ?>"><?= $sprint['points'] - $burnedPoints; ?></text>

    <text class="pointsBurned" x="<?= $previousX + .5; ?>" y="<?= $previousY - .5; ?>" opacity="0">
      <set attributeName="opacity" to="1" begin="day<?= $i ?>.mouseover;point<?= $i-1 ?>.mouseover" />
      <set attributeName="opacity" to="0" begin="day<?= $i ?>.mouseout;point<?= $i-1 ?>.mouseout" />
      <?= $sprint['dailyPoints'][$i]; ?>
    </text>
<? endfor; ?>

    <circle cx="0" cy="0" r="5" />
  </g><!-- /#chart-tasks -->



<?php
  /* add 0 if value is only one digit (1 -> 01) */
  function digitNumber($value) {
    return strlen($value) < 2 ? '0'.$value : $value;
  }

  /* global estimation */
  $globalSlope      = $y/$x;
  $globalYIntercept = $y - $x * $globalSlope;
  $globalX          = $graphWidth;
  $globalY          = $globalX * $globalSlope + $globalYIntercept;
  $globalDiff       = 100 - $globalY * 100 / $graphHeight;
  if($globalY > $graphHeight) {
    $globalY    = $graphHeight;
    $globalX    = $globalY / $globalSlope - $globalYIntercept;
    $globalDiff = 100 - $globalX * 100 / $graphwidth;
  }


  $globalRed    = round(100) == 0 ? '00' : 'ff';
  $globalGreen  = round(100) == 0 ? 187 : min( (1)*255, 210) - 75;
  $globalGreen  = $globalGreen < 0 ? '00' : digitNumber(dechex($globalGreen));
  $globalColor  = $globalRed.$globalGreen.'00';

  /* local estimation */
  $localSlope       = ($y - $previousY) / ($x - $previousX);
  $localYIntercept  = $y - $x * $localSlope;
  $localX           = $graphWidth;
  $localY           = $localSlope * $localX + $localYIntercept;
  $localDiff        = 100 - $localY * 100 / $graphHeight;
  if($localY > $graphHeight) {
    $localY     = $graphHeight;
    $localX     = ($localY - $localYIntercept) / $localSlope;
    $localDiff  = 100 - $localX * 100 / $graphWidth;
  }


  /* points left */
  if($globalDiff == 0){
    $legendglobalDiff = $legendlocalDiff = 'Success!';
  }
  else{
    $legendglobalDiff = $globalY < $graphHeight ? $globalDiff.'% left' : $globalDiff.'% more';
    $legendlocalDiff  = $localY < $graphHeight ? $localDiff.'% left' : $localDiff.'% more';
  }

  $blink  = round(100) == 0 ? '' : '<animate attributeName="fill" values="#'.$deviationColor.';#f00;#'.$deviationColor.'" dur="1" repeatCount="indefinite" />';

?>
  <g id="chart-global" fill="#<?= $globalColor; ?>" stroke="#<?= $globalColor; ?>" transform="translate(<?= $GraphMargin. ',' .$GraphMargin; ?>)">
    <line
      x1="0" y1="0"
      x2="<?= $globalX; ?>" y2="<?= $globalY; ?>"
      stroke-dasharray="50,10,10,10" stroke-width="2" />
    <circle cx="<?= $globalX; ?>" cy="<?= $globalY; ?>" r="5">
      <?= $blink; ?>
    </circle>
    <text x="<?= $globalX + .5; ?>" y="<?= $globalY + 1.5; ?>"><?= $legendglobalDiff; ?></text>
  </g>

  <g id="chart-local" transform="translate(<?= $GraphMargin. ',' .$GraphMargin; ?>)">
    <line
      x1="<?= $x; ?>" y1="<?= $y; ?>"
      x2="<?= $localX; ?>" y2="<?= $localY; ?>"
      stroke-dasharray="50,10,10,10" stroke-width="2" />
    <circle cx="<?= $localX; ?>" cy="<?= $localY; ?>" r="<?= 5 * pow($goalProjection+1, 3); ?>">
    </circle>
    <text x="<?= $localX + .5; ?>" y="<?= $localY + 1.5 + pow($goalProjection+.5, 3); ?>"><?= $legendlocalDiff; ?></text>
  </g>

  <script type="text/javascript">
    console.log(''
      +'\n GraphMargin:'+ <?= $GraphMargin; ?>
      +'\n unitDay:'+ <?= $unitDay; ?>
      +'\n ---'
      +'\n x:'+ <?= $x; ?>
      +'\n y:'+ <?= $y; ?>
      +'\n previousX:'+ <?= $previousX; ?>
      +'\n previousY:'+ <?= $previousY; ?>
      +'\n burnedPoints:'+ <?= $burnedPoints; ?>
      +'\n globalDiff:'+ <?= $globalDiff; ?>
      +'\n ---'
      +'\n globalSlope:'+ <?= $globalSlope; ?>
      +'\n globalX:'+ <?= $globalX; ?>
      +'\n globalY:'+ <?= $globalY; ?>
      +'\n ---'
      +'\n globalRed: <?= $globalRed; ?>'
      +'\n globalGreen: <?= $globalGreen; ?>'
      +'\n globalColor: <?= $globalColor; ?>'
      +'\n ---'
      +'\n localSlope:'+ <?= $localSlope; ?>
      +'\n localX:'+ <?= $localX; ?>
      +'\n localY:'+ <?= $localY; ?>
    );
  </script>
</svg>
