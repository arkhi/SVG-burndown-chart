<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <title>burndown chart in SVG</title>

  <link rel="stylesheet" href="common.css" />
</head>

<body>
  <table>
    <thead>
      <tr>
        <th>day</th>
        <th>points</th>
        <th>US points</th>
        <th>comment</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th>day 1</th>
        <td>12</td>
        <td>0</td>
        <td></td>
      </tr>
      <tr>
        <th>day 2</th>
        <td>21</td>
        <td>3</td>
        <td></td>
      </tr>
      <tr>
        <th>day 3</th>
        <td>17</td>
        <td>1</td>
        <td>We added some points to the user story here…</td>
      </tr>
      <tr>
        <th>day 4</th>
        <td>11</td>
        <td>5</td>
        <td></td>
      </tr>
    </tbody>
  </table>

<?php include('burndown_chart.php'); ?>

  <script src="tools.js"></script>
  <script>
    (function(){
      var polyline  = document.getElementById('chart-tasks').getElementsByTagName('polyline')[0];
      var p         = polyline.points.getItem(2);
      var numItems  = polyline.points.numberOfItems;
      console.log(numItems);
//      p.y = 300;

    })();
  </script>

</body>
</html>
