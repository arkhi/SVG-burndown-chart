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

  <!-- <script src="prototype.js"></script> -->
  <script src="tools.js"></script>
  <script>
    (function(){
      var polylineTasks = document.getElementById('chart-tasks').getElementsByTagName('polyline')[0];
      var polylineUS    = document.getElementById('chart-us').getElementsByTagName('polyline')[0];
      var label = {
        burndownChart : document.getElementById('svgBurndownChart'),
        grid          : document.getElementById('grid'),
        numItemsTasks : polylineTasks.points.numberOfItems,
        numItemsUS    : polylineUS.points.numberOfItems,
        xlinkns       : "http://www.w3.org/1999/xlink",

        /*
         * Allow easier creation of SVG elements in the DOM
         * thanks to Andrew Clover for
         * http://stackoverflow.com/questions/3642035/jquerys-append-not-working-with-svg-element/3642265#3642265
         *
         */
        makeSVG: function (tag, attrs) {
          var el= document.createElementNS('http://www.w3.org/2000/svg', tag);
          for (var k in attrs)
              el.setAttribute(k, attrs[k]);
          return el;
        },

        drawLabel: function(pointIndex) {
          var pTasks  = polylineTasks.points.getItem(pointIndex);
          var pUS     = polylineUS.points.getItem(pointIndex);

          /*
           * 150 is the margin used for the units in the graphic
           * -15,-45 are the coordinates to the center of the label
           *
           */
          var newLabelTasks = this.makeSVG('use', {
            'id'          : 'labelTask_'+ pointIndex,
            'class'       : 'labelTask',
            'x'           : pTasks.x + 150,
            'y'           : pTasks.y + 150,
            'width'       : 30,
            'height'      : 60,
            'fill'        : '#f00'
          });
          var newLabelUS = this.makeSVG('use', {
            'id'          : 'labelUS_'+ pointIndex,
            'class'       : 'labelUS',
            'x'           : pUS.x + 150,
            'y'           : pUS.y + 150,
            'width'       : 30,
            'height'      : 60,
            'fill'        : '#f00'
          });


          /*
           * We have to specify this attribute separately
           * as it doesn't use the same namespace.
           */
          newLabelTasks.setAttributeNS(this.xlinkns, "xlink:href", "#label");
          newLabelUS.setAttributeNS(this.xlinkns, "xlink:href", "#label");


          /*
           * Groups both labels together
           *
           */
          var newLabelGroup = this.makeSVG('g', {
            'id'          : 'labelGroup_'+ pointIndex,
            'transform'   : 'translate(-15,-45)',
            'opacity'     : 0
          });

          newLabelGroup.appendChild(newLabelTasks);
          newLabelGroup.appendChild(newLabelUS);


          /*
           * Creates the SVG events handler and assign them to the label
           *
           */
          var fadeIn = this.makeSVG('animate', {
            'attributeName' : 'opacity',
            'to'            : 1,
            'dur'           : '0.25s',
            'begin'         : 'listener_'+ pointIndex +'.mouseover',
            'fill'          : 'freeze'
          });
          var fadeOut = this.makeSVG('animate', {
            'attributeName' : 'opacity',
            'to'            : 0,
            'dur'           : '0.25s',
            'begin'         : 'listener_'+ pointIndex +'.mouseout',
            'fill'          : 'freeze'
          });

          newLabelGroup.appendChild(fadeIn);
          newLabelGroup.appendChild(fadeOut);

          this.burndownChart.appendChild(newLabelGroup);
        },

        drawListeners: function(){
          var abscissas     = document.getElementById('abscissa').getElementsByTagName('line');
          var abscissasNum  = abscissas.length;
          var listener = new Array();

          for(var i = 0; i < this.numItemsTasks ; i++) {
            var abscissaXValue = abscissas[i].x1.baseVal.value;
            var abscissaYValue = abscissas[i].y2.baseVal.value;

            listener[i] = this.makeSVG('rect', {
              'id'              : 'listener_'+ i,
              'class'           : 'listener',
              'x'               : abscissaXValue,
              'y'               : 0,
              'width'           : 50,
              'height'          : abscissaYValue,
              'fill'            : 'none',
              'transform'       : 'translate(-25,0)',
              'pointer-events'  : 'all'
            });

            this.grid.appendChild(listener[i]);
            label.drawLabel(i);
          }
        }
      }

      label.drawListeners();
    })();
  </script>

</body>
</html>
