<?php
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

  $graphPercent = 80;
  $GraphMargin  = (100 - $graphPercent) / 2;
  $withPoints   = $graphPercent / $sprint['points'];
  $widthDay     = $graphPercent / $sprint['days'];
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

  <g id="grid">
<?php for ($i = 0; $i < $sprint['points']; $i += 10) : /* horizontal lines: points */ ?>
    <line x1="<?= $GraphMargin; ?>%" y1="<?= $graphPercent + $GraphMargin - $i * $withPoints; ?>%" x2="<?= $graphPercent + $GraphMargin; ?>%" y2="<?= $graphPercent + $GraphMargin - $i * $withPoints; ?>%" stroke-dasharray="5,5" />
    <text x="<?= $GraphMargin -.5; ?>%" y="<?= $graphPercent + $GraphMargin - $i * $withPoints; ?>%" text-anchor="end" dominant-baseline="middle"><?= $i; ?></text>
<? endfor; ?>

<?php for ($i = 0; $i < $sprint['days']; $i += 5) : /* vertical lines: days */ ?>
    <line x1="<?= $graphPercent + $GraphMargin - $i * $widthDay; ?>%" y1="<?= $GraphMargin; ?>%" x2="<?= $graphPercent + $GraphMargin - $i * $widthDay; ?>%" y2="<?= $graphPercent + $GraphMargin; ?>%" />
  <?php if($i != 0) : ?>
    <text x="<?= $GraphMargin + $i * $widthDay; ?>%" y="<?= $graphPercent + $GraphMargin + .5; ?>%" text-anchor="middle" dominant-baseline="hanging">day <?= $i; ?></text>
  <? endif; ?>
<? endfor; ?>

    <!-- ideal line -->
    <line x1="<?= $GraphMargin; ?>%" y1="<?= $GraphMargin; ?>%" x2="<?= $GraphMargin + $graphPercent; ?>%" y2="<?= $GraphMargin + $graphPercent; ?>%" stroke="#eee" />

<?php for ($i = 0; $i <= $sprint['days']; $i++) : /* ideal tasks remaining: dots */ ?>
    <circle cx="<?= $GraphMargin + $i * $widthDay; ?>%" cy="<?= $GraphMargin + $i * $widthDay; ?>%" r="5" />
<? endfor; ?>

    <!-- frame -->
    <rect x="<?= $GraphMargin; ?>%" y="<?= $GraphMargin; ?>%" width="<?= $graphPercent; ?>%" height="<?= $graphPercent; ?>%" fill="none" stroke="#000" />
  </g><!-- /#grid -->

  <!-- That's our goal -->
  <circle cx="<?= 100 - $GraphMargin; ?>%" cy="<?= 100 - $GraphMargin; ?>%" r="5" fill="#090" stroke="#000" />



  <g id="legends">
    <text x="<?= $GraphMargin - 3; ?>%" y="<?= $GraphMargin - .5; ?>%" fill="#900">Tasks</text>
    <text x="<?= $GraphMargin - .5; ?>%" y="<?= $GraphMargin - .5; ?>%" fill="#900"><?= $sprint['points']; ?></text>
    <text x="<?= $GraphMargin - 3; ?>%" y="<?= $coordsModifier * $withPoints + $GraphMargin; ?>%" fill="#069">User Story</text>
  </g><!-- /#legends -->



  <g id="chart-us">
<?php for ($i = 0, $burnedPoints = 0; $i < count($sprint['dailyUSPoints']); $i++) : /* user stories */ ?>
  <?php
    $previousX      = ($i-1) * $widthDay + $widthDay + $GraphMargin;
    $previousY      = ($burnedPoints + $coordsModifier) * $withPoints + $GraphMargin;
    $burnedPoints   += $sprint['dailyUSPoints'][$i];
    $x              = $i * $widthDay + $widthDay + $GraphMargin;
    $y              = ($burnedPoints + $coordsModifier) * $withPoints + $GraphMargin;
  ?>
    <line x1="<?= $previousX; ?>%" y1="<?= $previousY; ?>%" x2="<?= $x; ?>%" y2="<?= $y; ?>%" />
<? endfor; ?>
<?php for ($i = 0, $burnedPoints = 0; $i < count($sprint['dailyUSPoints']); $i++) : /* user stories */ ?>
  <?php
    $previousX      = ($i-1) * $widthDay + $widthDay + $GraphMargin;
    $previousY      = ($burnedPoints + $coordsModifier) * $withPoints + $GraphMargin;
    $burnedPoints   += $sprint['dailyUSPoints'][$i];
    $x              = $i * $widthDay + $widthDay + $GraphMargin;
    $y              = ($burnedPoints + $coordsModifier) * $withPoints + $GraphMargin;
  ?>
    <circle cx="<?= $x; ?>%" cy="<?= $y; ?>%" r="5" />
    <text x="<?= $x + .5; ?>%" y="<?= $y - .5; ?>%"><?= $sprint['USPoints'] - $burnedPoints; ?></text>
<? endfor; ?>

    <circle cx="<?= $GraphMargin; ?>%" cy="<?= $coordsModifier * $withPoints + $GraphMargin; ?>%" r="5" />
    <text x="<?= $GraphMargin + .5; ?>%" y="<?= $coordsModifier * $withPoints + $GraphMargin -.5; ?>%" fill="#069"><?= $sprint['USPoints']; ?></text>
  </g><!-- /#chart-us -->



  <g id="chart-tasks">
<?php for ($i = 0, $burnedPoints = 0; $i < count($sprint['dailyPoints']); $i++) : /* tasks */ ?>
  <?php
    $previousX    = ($i-1) * $widthDay + $widthDay + $GraphMargin;
    $previousY    = $burnedPoints * $withPoints + $GraphMargin;
    $burnedPoints += $sprint['dailyPoints'][$i];
    $x            = $i * $widthDay + $widthDay + $GraphMargin;
    $y            = $burnedPoints * $withPoints + $GraphMargin;
  ?>
    <line id="day<?= $i ?>" x1="<?= $previousX; ?>%" y1="<?= $previousY; ?>%" x2="<?= $x; ?>%" y2="<?= $y; ?>%" />

<? endfor; ?>
<?php for ($i = 0, $burnedPoints = 0; $i < count($sprint['dailyPoints']); $i++) : /* tasks */ ?>
  <?php
    $previousX    = ($i-1) * $widthDay + $widthDay + $GraphMargin;
    $previousY    = $burnedPoints * $withPoints + $GraphMargin;
    $burnedPoints += $sprint['dailyPoints'][$i];
    $x            = $i * $widthDay + $widthDay + $GraphMargin;
    $y            = $burnedPoints * $withPoints + $GraphMargin;
  ?>
    <circle id="point<?= $i ?>" cx="<?= $x; ?>%" cy="<?= $y; ?>%" r="5" />
    <text x="<?= $x - .5; ?>%" y="<?= $y + .5; ?>%"><?= $sprint['points'] - $burnedPoints; ?></text>




    <text class="pointsBurned" x="<?= $previousX + .5; ?>%" y="<?= $previousY - .5; ?>%" opacity="0">
<!--
      <animate attributeName="opacity" to="1" dur="0.25" begin="day<?= $i ?>.mouseover;point<?= $i-1 ?>.mouseover" fill="freeze" />
      <animate attributeName="opacity" to="0" dur="0.25" begin="day<?= $i ?>.mouseout;point<?= $i-1 ?>.mouseout" fill="freeze" />
 -->

      <set attributeName="opacity" to="1" begin="day<?= $i ?>.mouseover;point<?= $i-1 ?>.mouseover" />
      <set attributeName="opacity" to="0" begin="day<?= $i ?>.mouseout;point<?= $i-1 ?>.mouseout" />
      <?= $sprint['dailyPoints'][$i]; ?>
    </text>
<? endfor; ?>

    <circle cx="<?= $GraphMargin; ?>%" cy="<?= $GraphMargin; ?>%" r="5" />
  </g><!-- /#chart-tasks -->



<?php

  /* deviation of global project */
  $ratioDeviation= ($y - $GraphMargin)/($x - $GraphMargin);
  if($ratioDeviation <= 1) {
    $deviationX  = 100 - $GraphMargin*2;
    $deviationY  = $deviationX * $ratioDeviation + $GraphMargin;
    $deviationX  = $deviationX + $GraphMargin;
  }
  else {
    $deviationY  = 100 - $GraphMargin*2;
    $deviationX  = $deviationY / $ratioDeviation + $GraphMargin;
    $deviationY  = $deviationY + $GraphMargin;
  }
  $goalDeviation = abs(1 - $ratioDeviation);

  /* projection for local estimation */
  $ratioProjection  = ($y - $previousY)/($x - $previousX);
  $threshold        = 100 - $GraphMargin;
  $daysLeft         = $sprint['days'] - ($x-$GraphMargin)/$widthDay;
  $projectionX      = $x;
  $projectionY      = $daysLeft * ($y - $previousY) + $y;

  if($projectionY > $threshold) {
    $projectionY  = $threshold;
  } else {
    $projectionX  = $daysLeft * $widthDay + $x;
    $projectionY  = $projectionY;
  }
  $goalProjection = abs(1 - $ratioProjection);

  function digitNumber($value) {
    return strlen($value) < 2 ? '0'.$value : $value;
  }
  $fillRed        = round($goalDeviation*100) == 0 ? '00' : 'ff';
  $fillGreen      = round($goalDeviation*100) == 0 ? 187 : min( (1-$goalDeviation)*255, 210) - 75;
  $fillGreen      = $fillGreen < 0 ? '00' : digitNumber(dechex($fillGreen));
/*
  $fillRed        = digitNumber(dechex( $goalDeviation*255 ));
  $fillGreen      = digitNumber(dechex( min( (1-$goalDeviation)*255, 187) ));
*/
  $deviationColor = $fillRed.$fillGreen.'00';
  $pointsLeft     = round($goalDeviation*100);
  $pointsLeftProj = round($goalProjection*100);
  if($pointsLeft == 0){
    $pointsLeft   = $pointsLeftProj = 'Success!';
  }
  else{
    $pointsLeft     = $ratioDeviation <= 1 ? $pointsLeft.'% left' : $pointsLeft.'% more';
    $pointsLeftProj = $ratioProjection <= 1 ? $pointsLeftProj.'% left' : $pointsLeftProj.'% more';
  }

  $blink          = round($goalDeviation*100) == 0 ? '' : '<animate attributeName="fill" values="#'.$deviationColor.';#f00;#'.$deviationColor.'" dur="1" repeatCount="indefinite" />';

?>
  <g id="chart-deviation" fill="#<?= $deviationColor; ?>" stroke="#<?= $deviationColor; ?>">
    <line
      x1="<?= $GraphMargin; ?>%" y1="<?= $GraphMargin; ?>%"
      x2="<?= $deviationX; ?>%" y2="<?= $deviationY; ?>%"
      stroke-dasharray="50,10,10,10" stroke-width="2" />
    <circle cx="<?= $deviationX; ?>%" cy="<?= $deviationY; ?>%" r="<?= 5 * pow($goalDeviation+1, 3); ?>">
      <?= $blink; ?>
    </circle>
    <text x="<?= $deviationX + .5; ?>%" y="<?= $deviationY + 1.5 + pow($goalDeviation+.5, 3); ?>%"><?= $pointsLeft; ?></text>
  </g>

  <g id="chart-projection">
    <line
      x1="<?= $x; ?>%" y1="<?= $y; ?>%"
      x2="<?= $projectionX; ?>%" y2="<?= $projectionY; ?>%"
      stroke-dasharray="50,10,10,10" stroke-width="2" />
    <circle cx="<?= $projectionX; ?>%" cy="<?= $projectionY; ?>%" r="<?= 5 * pow($goalProjection+1, 3); ?>">
    </circle>
    <text x="<?= $projectionX + .5; ?>%" y="<?= $projectionY + 1.5 + pow($goalProjection+.5, 3); ?>%"><?= $pointsLeftProj; ?></text>
  </g>

  <script type="text/javascript">
    console.log(''
      +'\n x:'+ <?= $x; ?>
      +'\n y:'+ <?= $y; ?>
      +'\n previousX:'+ <?= $previousX; ?>
      +'\n previousY:'+ <?= $previousY; ?>
      +'\n GraphMargin:'+ <?= $GraphMargin; ?>
      +'\n widthDay:'+ <?= $widthDay; ?>
      +'\n daysLeft:'+ <?= $daysLeft; ?>
      +'\n ---'
      +'\n ratioDeviation:'+ <?= $ratioDeviation; ?>
      +'\n goalDeviation:'+ <?= $goalDeviation; ?>
      +'\n deviationX:'+ <?= $deviationX; ?>
      +'\n deviationY:'+ <?= $deviationY; ?>
      +'\n ---'
      +'\n $x - $previousX:'+ <?= $x - $previousX; ?>
      +'\n $y - $previousY:'+ <?= $y - $previousY; ?>
      +'\n ratioProjection:'+ <?= $ratioProjection; ?>
      +'\n goalProjection:'+ <?= $goalProjection; ?>
      +'\n projectionX:'+ <?= $projectionX; ?>
      +'\n projectionY:'+ <?= $projectionY; ?>
      +'\n ---'
      +'\n fillRed: <?= $fillRed; ?>'
      +'\n fillGreen: <?= $fillGreen; ?>'
    );
  </script>
</svg>
