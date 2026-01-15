( function( mw, $, bs ) {
	bs.util.registerNamespace( 'bs.aggregatedStatistics.charts' );

	bs.aggregatedStatistics.charts.LineChart = function() {
		bs.aggregatedStatistics.charts.LineChart.super.call( this );
		this.width = 600;
		this.height = 300;
	};

	OO.inheritClass( bs.aggregatedStatistics.charts.LineChart, OO.ui.Widget );

	bs.aggregatedStatistics.charts.LineChart.prototype.setAxisLabels = function ( labels  ) {
		this.labels = Object.values( labels );
	};

	bs.aggregatedStatistics.charts.LineChart.prototype.updateData = function ( data ) {
		parseTime = d3.timeParse("%Y-%m-%d");

		data.forEach( function ( d ) {
			date = d.name;
			d.name = parseTime( date );
		});

		this.sumData = d3.group( data, d => d.line );
		this.data = data;
		this.viewchart();
	};

	bs.aggregatedStatistics.charts.LineChart.prototype.viewchart = function () {
		xScale = d3.scaleTime()
		.domain( d3.extent( this.data, function(d) { return d.name; } ) )
		.rangeRound( [ 50, this.width - 40 ] );

		yScale = d3.scaleLinear()
		.domain( [ 0, d3.max( this.data, d => d.value ) ] ).nice()
		.rangeRound( [ this.height - 30, 30 ] );

		var tickLength = 0;
		var mapItem = this.sumData.entries().next().value;
		tickLength = mapItem[1].length;

		this.xAxis = g => g
		.attr( "transform", `translate(0, ${ this.height - 30 } )` )
		.call( d3.axisBottom( xScale ).tickFormat( d3.timeFormat( "%Y-%m-%d" ) ) )
		.selectAll( "text" )
		.attr( "text-anchor", "end" )
		.attr( "transform", "rotate(-65)" )
		.attr( "font-size", "9px" );

		this.yAxis = g => g
		.attr( "transform", `translate( ${50} , 0 )`)
		.call( d3.axisLeft( yScale ));

		colorLine = d3.scaleOrdinal()
			.range( [ "#25401E", "#520610", "#84A63C","#B8D943","#EAF2AC",
			"#56732C", "#D95062","#D10F29","#521E25","#9E0B1F",
			"#2B283C", "#3E4557", "#D3BC76", "#D5AA46", "#DBC797",
			"#BFBFBF","#A6A6A6","#737373","#404040","#0D0D0D" ] );

		colorLabel = d3.scaleOrdinal()
			.range( [ "#25401E", "#520610", "#84A63C","#B8D943","#EAF2AC",
			"#56732C", "#D95062","#D10F29","#521E25","#9E0B1F",
			"#2B283C", "#3E4557", "#D3BC76", "#D5AA46", "#DBC797",
			"#BFBFBF","#A6A6A6","#737373","#404040","#0D0D0D" ] );

		this.chart = d3.create( "svg" )
		.append( "svg" )
		.attr( "viewBox", [ 0, 0, this.width, this.height + 90 ] );

		this.chart.append( "g" ).call( this.xAxis );
		this.chart.append('g')
		.attr("class", "grid")
		.attr("transform", `translate(0, ${ this.height - 30 } )`)
		.call(d3.axisBottom( xScale )
		.tickSize( -this.height + 50 , 20, 0)
		.tickFormat(''));

		this.chart.append( "g" ).call( this.yAxis );
		this.chart.append('g')
		.attr("class", "grid")
		.attr( "transform", `translate( ${50} , 0 )`)
		.call(d3.axisLeft( yScale )
		.tickSize( -this.width , 0, 0)
		.tickFormat(''));

		this.chart.append("text")
		.attr("transform", "rotate(-90)")
		.attr("x", 0 - (this.height / 2) )
		.attr("y",  10 )
		.attr("text-anchor", "middle")
		.style( "font-size", "9px" )
		.text( this.labels[0] );

		this.chart.selectAll(".line")
		.data(this.sumData)
		.enter()
		.append("g")
		.append("path")
		.attr("fill", "none")
		.attr("stroke", function(d) {
			color = colorLine( d );
			return color; })
		.attr("stroke-width", 1.5)
		.attr("d", function(d) {
		return d3.line()
			.x(function(d) { return xScale(d.name); })
			.y(function(d) { return yScale(d.value); })
			(d[1]);
		});

		this.chart.selectAll( ".line" )
		.data(this.data)
		.enter()
		.append( "circle" )
		.attr("cx", function(d) { return xScale(d.name); })
		.attr("cy", function(d) { return yScale(d.value); })
		.attr( "r",  12 )
		.attr( "fill", 'grey' )
		.attr("opacity", 0.2)
		.on( 'mouseenter', function ( actual, i ) {
			d3.select(this)
			.attr('opacity', 0.6);

			$tooltip.html( i.value )
			.css('visibility', 'visible')
			.css("top", ( actual.y + 90 + 'px'))
			.css("left", actual.x + 'px' )
			.attr("height", "30px")
			.attr("width", "50px");
		})
		.on('mousemove', function (actual, i) {
			$tooltip.css("top", ( actual.y + 90 + 'px'))
			.css("left", actual.x + 'px' );
		})
		.on('mouseout', function (actual, i) {
			d3.select(this).attr('opacity', 0.2);
			$tooltip
			.css('visibility', "hidden");
		});

		var lines = this.chart.selectAll(".line")
			.data( this.sumData )
			.enter()
			.append( "text" )
			.attr("class", "serie-label")
			.attr( "transform" , function(d) {
				return "translate( " + ( xScale(d[1][d[1].length -1 ].name) ) + ","
				+ ( yScale(d[1][d[1].length -1 ].value) + 10) + ")";
			})
			.attr( "x", 3)
			.attr( "dy" , ".35em" )
			.text( function(d) { return d[1][d[1].length -1 ].line })
			.attr( "text-anchor", "middle" )
			.style( "fill", function(d) { return color; } )
			.style( "font-size", "9px" );

		var $tooltip = $('<div>')
			.attr('class', 'abc')
			.css("visibility", 'hidden')
			.css("background-color", "white")
			.css("border", "solid")
			.css("border-width", "1px")
			.css("border-radius", "5px")
			.css("padding", "10px")
			.css("position", "absolute");

			$tooltip.appendTo( 'body' );

		this.$element =  this.chart;
	};

} )( mediaWiki, jQuery, blueSpice );
