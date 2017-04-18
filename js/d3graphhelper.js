 // var data = []
 // var distArray = [];//making these universally accesible so we dont need to call the elevation API everytime I want the data
 // var elevArray = [];
 // var elevationPath = [];

 function initializeGraph(results, status) {
        if (status != google.maps.ElevationStatus.OK) {
          return;
        }

        // Extract the elevation samples from the returned results
        // and store them in an array of LatLngs.
        elevationPath = [];

        var Point = function(dist, elev) {//this is the object I had to create so that when the dataarray is converted to JSON it is in the format most compatible with d3
            this.distance = dist;
            this.elevation = elev;
        };


        data = [];
        distArray = [];//these are for the indexOf() fuction
        elevArray = [];
        var totalDist = 0;
        for (var i = 0; i < results.length; i++) {

          elevationPath.push(results[i].location);

          if(i > 0){
            totalDist += calcDistance(elevationPath[i],elevationPath[i-1]);
          }
          distArray[i] = Math.round(totalDist*10)/10;

          elevArray[i] = Math.round(results[i].elevation);

          if (typeof elevationData !== 'undefined') { //this is just horrible coding practice right here, need to fix this...
            elevationData[i] = results[i].elevation; //THIS IS WHY U DONT USE GLOBAL VARIABLES
          }

          data.push(new Point(distArray[i],Math.round(results[i].elevation)));//add object to data array
        }

        data = JSON.parse(JSON.stringify(data)); //converting data[] to json then back from json to a json array

        var margin = {top: 10, right: 0, bottom: 20, left: 40},//setting margins of graph
            width = $("#elevationGraph").width() - margin.left - margin.right,
            height = $("#elevationGraph").height() - margin.top - margin.bottom;


        var x = d3.scale.linear().range([0, width]); //making the graph the width of the div

        var y = d3.scale.linear().range([height, 0]); //making the graph the height of the div

        var xAxis = d3.svg.axis()
            .scale(x)
            .orient("bottom")
            .tickSize(-height,0)
            .tickSubdivide(true)
            .ticks(10)
            .tickFormat(//making vertical lines the height of the graph with tickSize(-height) and also adding km suffix to labels, will have to add option of miles
              function (d) {
                d = Math.round(d) + ".0 km";
                return d;
              });

        var yAxis = d3.svg.axis()
            .scale(y)
            .orient("left")
            .ticks(5)
            .tickSize(-width,6)
            .tickFormat(//making horizontal lines the width of the graph with tickSize(-width) and also adding m suffix to labels, will have to add option of feet
              function (d) {
                d = d + " m";
                return d;
              });


        var area = d3.svg.area() //adding area under the line to graph
            .x(function(d) { return x(d.distance); })
            .y0(height)
            .y1(function(d) { return y(d.elevation); });

        var line = d3.svg.line()//adding line to graph overtop of the area so that the path doesnt highlight all four sides of graph and only highlights top
            .x(function(d) { return x(d.distance); })
            .y(function(d) { return y(d.elevation); });

        var svg = d3.select("#elevationGraph").append("svg")
            .attr("width", width + margin.left + margin.right)//setting total width of graph
            .attr("height", height + margin.top + margin.bottom)//setting total height of graph
              .append("g")
                .attr("transform", "translate(" + margin.left + "," + margin.top + ")");//not quite sure what this does but I think it is moving the actual graph and not the x or y acis


        x.domain(d3.extent(data, function(d) { return d.distance; })); //making the domain of the x axis the distance of the ride
        y.domain([d3.min(data, function(d) { return d.elevation; }), d3.max(data, function(d) { return d.elevation; })]);//making the domain of th y axis the minimum->maximum elevations reached in the ride

        svg.append("g")//adding x axis to graph
            .attr("class", "x axis")
            .attr("transform", "translate(0," + height + ")")
            .call(xAxis);

        svg.append("g")//adding y axis to graph
            .attr("class", "y axis")
            .call(yAxis)
            .append("text")
            .attr("transform", "rotate(-90)")
            .attr("y", 6)
            .attr("dy", ".71em");

        svg.append("path")//adding area to graph
            .datum(data)
            .attr("class", "area")
            .attr("d", area);

        var path = svg.append("path")//adding line to graph
          .datum(data)
          .attr("class", "line")
          .attr("d", line);

        var hoverLineGroup = svg.append("g")//adding vertical tooltip line
          .attr("class", "hover-line");

          var hoverLine = hoverLineGroup//adding attributes to vertical tool tip line so that it moves with cursor
            .append("line")
            .attr("class", "hover-line-tooltip")
              .attr("x1", 10).attr("x2", 10)
              .attr("y1", 0).attr("y2", height); 

        var hoverSquareGroup = svg.append("g")//adding tooltip that contains info at x point on graph
          .attr("class", "hover-square");

          var hoverSquare = hoverSquareGroup//adding attributes to vertical tool tip so that it moves with cursor
            .append("rect")
            .attr("class","hover-square-rect")
              .attr("x", 0).attr("y", 20) 
              .attr("width", 150).attr("height", 63);

          var hoverTextDist = hoverSquareGroup//adding text to tooltip
            .append("text")
            .attr("x", 5)//x and y coordinates within the rect of the tooltip for distance text
            .attr("y", 61)
            .attr("dy", ".35em")
            .attr("class", "hover-text-dist")
            .text("0 m");

          var hoverTextElev = hoverSquareGroup
            .append("text")
            .attr("x", 5)// x and y coordinates within the rect of the tooltip for elevation
            .attr("y", 41)
            .attr("dy", ".35em")
            .attr("class", "hover-text-elev")
            .text("0 m");


        var pathEl = path.node();
        var pathLength = pathEl.getTotalLength();

        hoverLineGroup.style("opacity", 1e-6);//hide vertical line and tooltip as well as mousemarker when not hovering over graph
        hoverSquareGroup.style("opacity", 1e-6);


        _climb = getAscent(results);
       initiateToolTip(margin,x,y,width,pathEl,pathLength);
      }

function updateGraph(results, status) {
        if (status != google.maps.ElevationStatus.OK) {//this will trigger and not update graph if requested to frequently, need to pause about 3 seconds in between calls
          return;
        }

        // Extract the elevation samples from the returned results
        // and store them in an array of LatLngs.
        elevationPath = [];

        var Point = function(dist, elev) {//this is the object I had to create so that when the dataarray is converted to JSON it is in the format most compatible with d3
            this.distance = dist;
            this.elevation = elev;
        };

        data = [];
        distArray = [];//need to empty arrays
        elevArray = [];
        var totalDist = 0;
        for (var i = 0; i < results.length; i++) {
          elevationPath.push(results[i].location);

          if(i > 0){
            totalDist += calcDistance(elevationPath[i],elevationPath[i-1]);
          }
          distArray[i] = Math.round(totalDist*10)/10;

          distArray[i] = Math.round((i/results.length) * route['distance'] * 10)/10;
          elevArray[i] = Math.round(results[i].elevation);

          elevationData[i] = results[i].elevation;//declaring global variable for processRoute

          data.push(new Point(distArray[i],Math.round(results[i].elevation)));//add object to data array
        }


        data = JSON.parse(JSON.stringify(data)); //converting data[] to json then back from json to a json array

         var margin = {top: 10, right: 0, bottom: 20, left: 40},//setting margins of graph
            width = $("#elevationGraph").width() - margin.left - margin.right,
            height = $("#elevationGraph").height() - margin.top - margin.bottom;

       
        var x = d3.scale.linear().range([0, width]); //making the graph the width of the div

        var y = d3.scale.linear().range([height, 0]); //making the graph the height of the div

        x.domain(d3.extent(data, function(d) { return d.distance; })); //making the domain of the x axis the distance of the ride
        y.domain([d3.min(data, function(d) { return d.elevation; }), d3.max(data, function(d) { return d.elevation; })]);//making the domain of th y axis the minimum->maximum elevations reached in the ride

        var svg = d3.select('#elevationGraph').transition();


        var xAxis = d3.svg.axis()
            .scale(x)
            .orient("bottom")
            .tickSize(-height,0)
            .tickSubdivide(true)
            .ticks(10)
            .tickFormat(//making vertical lines the height of the graph with tickSize(-height) and also adding km suffix to labels, will have to add option of miles
              function (d) {
                d = Math.round(d) + ".0 km";
                return d;
              });

        var yAxis = d3.svg.axis()
            .scale(y)
            .orient("left")
            .ticks(5)
            .tickSize(-width,6)
            .tickFormat(//making horizontal lines the width of the graph with tickSize(-width) and also adding m suffix to labels, will have to add option of feet
              function (d) {
                d = d + " m";
                return d;
              });

        var area = d3.svg.area() //adding area under the line to graph
          .x(function(d) { return x(d.distance); })
          .y0(height)
          .y1(function(d) { return y(d.elevation); });

        var line = d3.svg.line()//adding line to graph overtop of the area so that the path doesnt highlight all four sides of graph and only highlights top
          .x(function(d) { return x(d.distance); })
          .y(function(d) { return y(d.elevation); });

        

        svg.select(".x.axis") // change the x axis
            .call(xAxis);
        svg.select(".y.axis") // change the y axis
            .call(yAxis);

        d3.select('.area').attr('d',area(data));
        d3.select('.line').attr('d',line(data));

        var hoverLineGroup = d3.select('.hover-line')

          var hoverLine = d3.select('.hover-line-tooltip')

        var hoverSquareGroup = d3.select('.hover-square');

          var hoverSquare = d3.select('.hover-square-rect');

          var hoverTextDist = d3.select('.hover-text-dist');

          var hoverTextElev = d3.select('.hover-text-elev');

        var path = d3.select('.line');
        var pathEl = path.node();
        var pathLength = pathEl.getTotalLength();

        _climb = getAscent(results);
        initiateToolTip(margin,x,y,width,pathEl,pathLength);
        
}

 function initializeRouteViewerGraph(elevationData,route) {

        // Extract the elevation samples from the returned results
        // and store them in an array of LatLngs.
        elevationPath = [];

        var Point = function(dist, elev) {//this is the object I had to create so that when the dataarray is converted to JSON it is in the format most compatible with d3
            this.distance = dist;
            this.elevation = elev;
        };


        data = [];
        distArray = [];//these are for the indexOf() fuction
        elevArray = [];
        var totalDist = 0;
        for (var i = 0; i < elevationData.length; i++) {

          coordIndex = Math.round((i/elevationData.length)*route['routeCoords'].length);// this allows elevationPath to work if there are more coordinate points then elevation points (only happens if elevation data is from google maps)

          elevationPath.push(new google.maps.LatLng(route['routeCoords'][coordIndex][0],route['routeCoords'][coordIndex][1]));
          if(i > 0){
            totalDist += calcDistance(elevationPath[i],elevationPath[i-1]);
          }
          distArray[i] = Math.round(totalDist*10)/10;
          elevArray[i] = Math.round(elevationData[i]);

          data.push(new Point(distArray[i],Math.round(elevationData[i])));//add object to data array
        }

        data = JSON.parse(JSON.stringify(data)); //converting data[] to json then back from json to a json array

        var margin = {top: 10, right: 0, bottom: 20, left: 40},//setting margins of graph
            width = $("#elevationGraph").width() - margin.left - margin.right,
            height = $("#elevationGraph").height() - margin.top - margin.bottom;


        var x = d3.scale.linear().range([0, width]); //making the graph the width of the div

        var y = d3.scale.linear().range([height, 0]); //making the graph the height of the div

        var xAxis = d3.svg.axis()
            .scale(x)
            .orient("bottom")
            .tickSize(-height,0)
            .tickSubdivide(true)
            .ticks(10)
            .tickFormat(//making vertical lines the height of the graph with tickSize(-height) and also adding km suffix to labels, will have to add option of miles
              function (d) {
                d = Math.round(d) + ".0 km";
                return d;
              });

        var yAxis = d3.svg.axis()
            .scale(y)
            .orient("left")
            .ticks(5)
            .tickSize(-width,6)
            .tickFormat(//making horizontal lines the width of the graph with tickSize(-width) and also adding m suffix to labels, will have to add option of feet
              function (d) {
                d = d + " m";
                return d;
              });


        var area = d3.svg.area() //adding area under the line to graph
            .x(function(d) { return x(d.distance); })
            .y0(height)
            .y1(function(d) { return y(d.elevation); });

        var line = d3.svg.line()//adding line to graph overtop of the area so that the path doesnt highlight all four sides of graph and only highlights top
            .x(function(d) { return x(d.distance); })
            .y(function(d) { return y(d.elevation); });

        var svg = d3.select("#elevationGraph").append("svg")
            .attr("width", width + margin.left + margin.right)//setting total width of graph
            .attr("height", height + margin.top + margin.bottom)//setting total height of graph
              .append("g")
                .attr("transform", "translate(" + margin.left + "," + margin.top + ")");//not quite sure what this does but I think it is moving the actual graph and not the x or y acis


        x.domain(d3.extent(data, function(d) { return d.distance; })); //making the domain of the x axis the distance of the ride
        y.domain([d3.min(data, function(d) { return d.elevation; }), d3.max(data, function(d) { return d.elevation; })]);//making the domain of th y axis the minimum->maximum elevations reached in the ride

        svg.append("g")//adding x axis to graph
            .attr("class", "x axis")
            .attr("transform", "translate(0," + height + ")")
            .call(xAxis);

        svg.append("g")//adding y axis to graph
            .attr("class", "y axis")
            .call(yAxis)
            .append("text")
            .attr("transform", "rotate(-90)")
            .attr("y", 6)
            .attr("dy", ".71em");

        svg.append("path")//adding area to graph
            .datum(data)
            .attr("class", "area")
            .attr("d", area);

        var path = svg.append("path")//adding line to graph
          .datum(data)
          .attr("class", "line")
          .attr("d", line);

        var hoverLineGroup = svg.append("g")//adding vertical tooltip line
          .attr("class", "hover-line");

          var hoverLine = hoverLineGroup//adding attributes to vertical tool tip line so that it moves with cursor
            .append("line")
            .attr("class", "hover-line-tooltip")
              .attr("x1", 10).attr("x2", 10)
              .attr("y1", 0).attr("y2", height); 

        var hoverSquareGroup = svg.append("g")//adding tooltip that contains info at x point on graph
          .attr("class", "hover-square");

          var hoverSquare = hoverSquareGroup//adding attributes to vertical tool tip so that it moves with cursor
            .append("rect")
            .attr("class","hover-square-rect")
              .attr("x", 0).attr("y", 20) 
              .attr("width", 150).attr("height", 63);

          var hoverTextDist = hoverSquareGroup//adding text to tooltip
            .append("text")
            .attr("x", 5)//x and y coordinates within the rect of the tooltip for distance text
            .attr("y", 61)
            .attr("dy", ".35em")
            .attr("class", "hover-text-dist")
            .text("0 m");

          var hoverTextElev = hoverSquareGroup
            .append("text")
            .attr("x", 5)// x and y coordinates within the rect of the tooltip for elevation
            .attr("y", 41)
            .attr("dy", ".35em")
            .attr("class", "hover-text-elev")
            .text("0 m");


        var pathEl = path.node();
        var pathLength = pathEl.getTotalLength();

        hoverLineGroup.style("opacity", 1e-6);//hide vertical line and tooltip as well as mousemarker when not hovering over graph
        hoverSquareGroup.style("opacity", 1e-6);

       initiateToolTip(margin,x,y,width,pathEl,pathLength);
      }

function updateGraphWidth(){
   var svg = d3.select('#elevationGraph');

   if(data.length == 0){
    return;
   }

   var margin = {top: 10, right: 0, bottom: 20, left: 40},//setting margins of graph
      width = $("#elevationGraph").width() - margin.left - margin.right,
      height = $("#elevationGraph").height() - margin.top - margin.bottom;
    d3.select('.line').data(data, function(d) { return d.name; })

    var x = d3.scale.linear().range([0, width]); //making the graph the width of the div

    var y = d3.scale.linear().range([height, 0]); //making the graph the height of the div

        x.domain(d3.extent(data, function(d) { return d.distance; })); //making the domain of the x axis the distance of the ride
        y.domain([d3.min(data, function(d) { return d.elevation; }), d3.max(data, function(d) { return d.elevation; })]);//making the domain of th y axis the minimum->maximum elevations reached in the ride

     var xAxis = d3.svg.axis()
            .scale(x)
            .orient("bottom")
            .tickSize(-height,0)
            .tickSubdivide(true)
            .ticks(10)
            .tickFormat(//making vertical lines the height of the graph with tickSize(-height) and also adding km suffix to labels, will have to add option of miles
              function (d) {
                d = Math.round(d) + ".0 km";
                return d;
              });

        var yAxis = d3.svg.axis()
            .scale(y)
            .orient("left")
            .ticks(5)
            .tickSize(-width,6)
            .tickFormat(//making horizontal lines the width of the graph with tickSize(-width) and also adding m suffix to labels, will have to add option of feet
              function (d) {
                d = d + " m";
                return d;
              });

      var area = d3.svg.area() //adding area under the line to graph
        .x(function(d) { return x(d.distance); })
        .y0(height)
        .y1(function(d) { return y(d.elevation); });

      var line = d3.svg.line()//adding line to graph overtop of the area so that the path doesnt highlight all four sides of graph and only highlights top
        .x(function(d) { return x(d.distance); })
        .y(function(d) { return y(d.elevation); });

      svg.select(".x.axis") // change the x axis
          .call(xAxis);
      svg.select(".y.axis") // change the y axis
          .call(yAxis);


      d3.select('.area').attr('d',area(data));
      d3.select('.line').attr('d',line(data));


      svg.select('svg')
        .attr("width", width + margin.left + margin.right)//setting total width of graph
        .attr("height", height + margin.top + margin.bottom)//setting total height of graph

      

      var path = d3.select('.line');
      var pathEl = path.node();
      var pathLength = pathEl.getTotalLength();

     initiateToolTip(margin,x,y,width,pathEl,pathLength);
}

function initiateToolTip(margin,x,y,width,pathEl,pathLength){

  var hoverLineGroup = d3.select('.hover-line')

  var hoverLine = d3.select('.hover-line-tooltip')

  var hoverSquareGroup = d3.select('.hover-square');

  var hoverSquare = d3.select('.hover-square-rect');

  var hoverTextDist = d3.select('.hover-text-dist');

  var hoverTextElev = d3.select('.hover-text-elev');

  var mousemarker = new google.maps.Marker({//marker that appears on map in coordination with where the user hovers over the graph
        map: biketime.googleMaps.map,
        icon: "../images/cycling_inverse_blue.png"
      });

    d3.select('#elevationGraph').on("mouseover", function() { 
      //console.log('mouseover')
      mousemarker.setMap(biketime.googleMaps.map);
    }).on("mousemove", function() {
      var mouse_x = d3.mouse(this)[0] - margin.left;//adjust y-axis offset so that the axis is included in the graph
      var mouse_y = d3.mouse(this)[1];
      var graph_y = y.invert(mouse_y);
      var graph_x = x.invert(mouse_x);

      var index = distArray.indexOf(Math.round(graph_x *10)/10); //graph_x is the value of the x coordinate where the user is hovering over, I am getting the index of that distance from the distAray
      var yVal = data[index]; //the distance and elevation array are the same length so the index of distance x in the distance array will have the corresponding elevation in the elvation array, thus giving the elevation at point x
      if(mouse_x > 0){//only showing the vertical line and tooltip if the user hovers over the graph and not the y-axis
        hoverLine.attr("x1", mouse_x).attr("x2", mouse_x)//setting x location of hoverline
        hoverLineGroup.style("opacity", 1); //making hoverline visible

        var beginning = mouse_x, end = pathLength, target;//dont entirely know what algorithm does step-by-step but it is getting the value of y on the line graph at the x coordinate the user is hovered over
          while (true) {
            target = Math.floor((beginning + end) / 2);
            pos = pathEl.getPointAtLength(target);
            if ((target === end || target === beginning) && pos.x !== x) {
                break;
            }
            if (pos.x > mouse_x)      end = target;
            else if (pos.x < mouse_x) beginning = target;
            else                break; //position found
          }

        
        mouse_x = (width - mouse_x < 150) ? mouse_x -150 : mouse_x; // move tooltip to left of hoverLine so it isnt hidden when at right side of graph

        hoverSquareGroup.attr("transform","translate("+ (mouse_x) +",0)").style("opacity", 1);//move tooltip with cursor
        hoverTextDist.text("Dist: " + Math.round(graph_x * 10)/10 + " km");//update text of tooltip
        hoverTextElev.text("Elev: " + Math.round(y.invert(pos.y)) + " m");

        if(elevationPath[index] != null)
          mousemarker.setPosition(elevationPath[index]);//update position of marker
      }
        
    })  .on("mouseout", function() {
        //console.log('mouseout');
        hoverLineGroup.style("opacity", 1e-6);//hide vertical line and tooltip as well as mousemarker when not hovering over graph
        hoverSquareGroup.style("opacity", 1e-6);
        mousemarker.setMap(null);
      });
  }


  function getAscent(r) {
    var prevElevation = r[0].elevation;
    var climb = 0;
    var drop = 0;
    var max = 0;
    for (var i = 1; i < r.length; i++) {
      var diff = r[i].elevation - prevElevation;
      prevElevation = r[i].elevation;
      if (diff > 0) {
        climb += diff;
        climb = Math.abs(climb / 1) ;//Object to number
        var  climb2 = climb  * 3.2808399;//Metres to feet
      }
      else {
        drop -= diff;
        drop = Math.abs(drop / 1) ;
        var  drop2 = drop  * 3.2808399;
      }

      if (r[i].elevation > max) {
        max = r[i].elevation;
      }
    }
    max = Math.ceil(max);
    $('#elevation').text(Math.round(climb) + 'm');
    return climb;
  }

  function calcDistance(p1, p2){
  return (google.maps.geometry.spherical.computeDistanceBetween(p1, p2) / 1000)
}