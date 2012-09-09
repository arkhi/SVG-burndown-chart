<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <title>burndown chart in SVG</title>

  <link rel="stylesheet" href="common.css" />
</head>

<body>
  <table id="sprintStats">
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
        <td>Some surprise here…</td>
      </tr>
      <tr>
        <th>day 4</th>
        <td>23</td>
        <td>5</td>
        <td></td>
      </tr>
      <tr>
        <th>day 5</th>
        <td>12</td>
        <td>0</td>
        <td>another one there…</td>
      </tr>
    </tbody>
  </table>

<?php include('burndown_chart.php'); ?>

  <script src="tools.js"></script>
</body>
</html>
