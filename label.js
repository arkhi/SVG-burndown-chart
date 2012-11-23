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

    for(var i = 1; i < this.numItemsTasks ; i++) {
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
        'pointer-events'  : 'all'
      });

      this.grid.appendChild(listener[i]);
    }
  }

  // console.log(''
  //   +'\n GraphMargin: '+ <?= $GraphMargin; ?>
  //   +'\n unitDay: '+ <?= $unitDay; ?>
  //   +'\n ---'
  //   +'\n x: '+ <?= $x; ?>
  //   +'\n y: '+ <?= $y; ?>
  //   +'\n previousX: '+ <?= $previousX; ?>
  //   +'\n previousY: '+ <?= $previousY; ?>
  //   +'\n burnedPoints: '+ <?= $burnedPoints; ?>
  //   +'\n ---'
  //   +'\n globalSlope: '+ <?= $globalSlope; ?>
  //   +'\n globalX: '+ <?= $globalX; ?>
  //   +'\n globalY: '+ <?= $globalY; ?>
  //   +'\n globalDiff: '+ <?= $globalDiff; ?>
  //   +'\n globalDiffAbs: '+ <?= $globalDiffAbs; ?>
  //   +'\n ---'
  //   +'\n globalRed: <?= $globalRed; ?>'
  //   +'\n globalGreen: <?= $globalGreen; ?>'
  //   +'\n globalColor: <?= $globalColor; ?>'
  //   +'\n ---'
  //   +'\n localSlope: '+ <?= $localSlope; ?>
  //   +'\n localX: '+ <?= $localX; ?>
  //   +'\n localY: '+ <?= $localY; ?>
  //   +'\n localDiff: '+ <?= $localDiff; ?> +''
  // );
}
