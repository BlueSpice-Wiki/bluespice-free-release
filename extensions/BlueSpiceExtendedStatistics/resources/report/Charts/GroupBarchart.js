( function( mw, $, bs ) {

	bs.util.registerNamespace( 'bs.aggregatedStatistics.charts' );

	bs.aggregatedStatistics.charts.Groupchart = function ( cfg ) {
		bs.aggregatedStatistics.charts.Groupchart.super.call( this, cfg );
		this.width = 600;
		this.height = 400;
		this.names = [];
	};

	OO.inheritClass( bs.aggregatedStatistics.charts.Groupchart, OO.ui.Widget );

	bs.aggregatedStatistics.charts.Groupchart.prototype.setAxisLabels = function ( labels  ) {
		this.labels = Object.values( labels );
		this.axisLabel = this.labels[0];
		this.labels.shift();
	};

	bs.aggregatedStatistics.charts.Groupchart.prototype.updateData = function ( data ) {
		this.data = data;
		this.names = Object.keys( this.data[0] ).filter( function ( key ) {
			return key !== "name" && key !== "values";
		});
		names = this.names;
		this.data.forEach( function ( d ) {
			d.values = names.map( function ( name ) {
				return { name:name, values: d[name] };
			});
		});
		this.viewchart();
	};

	bs.aggregatedStatistics.charts.Groupchart.prototype.viewchart = function () {
		xScale = d3.scaleBand()
			.domain( this.data.map( d => d.name ) )
			.rangeRound( [ 50, this.width - 10 ] )
			.paddingInner( 0.1 );

		xInner = d3.scaleBand()
			.domain( this.names )
			.rangeRound( [ 0, xScale.bandwidth() ])
			.padding( 0.05 );

		yScale = d3.scaleLinear()
			.domain( [ 0, d3.max( this.data, function ( d ) { return d3.max( d.values, function(d) { return d.values; } ); } ) ] ).nice()
			.rangeRound( [ this.height - 80, 30 ] );

		this.colorChart = d3.scaleOrdinal()
			.range( [ "#98abc5", "#8a89a6", "#7b6888", "#6b486b", "#a05d56", "#d0743c", "#ff8c00" ] );
		this.colorLabel = d3.scaleOrdinal()
			.range( [ "#98abc5", "#8a89a6", "#7b6888", "#6b486b", "#a05d56", "#d0743c", "#ff8c00" ] );

		this.xAxis = g => g
			.attr( "transform", `translate(0, ${ this.height - 80 } )` )
			.call( d3.axisBottom( xScale ).tickSizeOuter( 0 ) )
			.selectAll( "text" )
			.attr( "text-anchor", "end" )
			.attr( "transform", "rotate(-65)" )
			.attr( "font-size", "9px" );

		this.yAxis = g => g
			.attr( "transform", `translate( ${50} , 0 )`)
			.call( d3.axisLeft( yScale ).ticks( null, "s" ) );

		this.chart = d3.create( "svg" )
				.append( "svg" )
				.attr( "viewBox", [ 0, 0, this.width, this.height ] );
		this.chart.append( "g" )
				.call( this.xAxis );

		this.chart.append( "g" )
				.call( this.yAxis );
		this.chart.append('g')
			.attr("class", "grid")
			.attr( "transform", `translate( ${50} , 0 )`)
			.call(d3.axisLeft( yScale )
			.tickSize( -this.width  , 0, 0)
			.tickFormat(''));

		this.chart.append("text")
			.attr("transform", "rotate(-90)")
			.attr("x", 0 - (this.height / 2) )
			.attr("y",  10 )
			.attr("text-anchor", "middle")
			.style( "font-size", "9px" )
			.text( this.axisLabel );

		this.chart.append( "g" )
				.selectAll( "g" )
				.data( this.data )
				.join( "g" )
				.attr( "transform", d => `translate( ${xScale( d.name ) } ,0)`)
				.selectAll( "rect" )
				.data( function ( d ) { return d.values } )
				.join( "rect" )
				.attr( "x", d => xInner( d.name ) )
				.attr( "y", d => yScale( d.values ) )
				.attr( "width", xInner.bandwidth() )
				.attr( "height", d =>  yScale( 0 ) - yScale( d.values ) )
				.attr( "fill", d => this.colorChart( d.name ) )
				.on( 'mouseenter', function ( actual, i ) {
					d3.select(this)
					.attr('opacity', 0.6);
					$tooltip.html( i.values )
					.css('visibility', 'visible')
					.css("top", ( actual.y + 120 + 'px'))
					.css("left", actual.x + 'px' )
					.attr("height", "30px")
					.attr("width", "50px");
				})
				.on('mousemove', function (actual, i) {
					$tooltip.css("top", ( actual.y + 90 + 'px'))
					.css("left", actual.x + 'px' )
				})
				.on('mouseout', function (actual, i) {
					d3.select(this).attr('opacity', 1);
					$tooltip
					.css('visibility', "hidden");
				});

		var legend= this.chart.selectAll( ".legend" )
				.data( this.labels ).enter().append( "g" )
				.attr( "class", "legend" )
				.attr( "transform", function( d, i ) { return "translate( 0, " + i * 20 + ")"; } );

		legend.append( "rect" )
				.attr( "x", this.width - 18 )
				.attr( "width", 18 )
				.attr( "height", 18 )
				.style( "fill",  d => this.colorLabel( d ) );

		legend.append( "text" )
				.attr( "x", this.width -24 )
				.attr( "y", 9 )
				.attr( "dy" , ".35em" )
				.style( "text-anchor", "end" )
				.style( "font-size", "9px" )
				.text( function ( d ) { return d; });

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

		this.$element = this.chart;
	};

})( mediaWiki, jQuery, blueSpice );
