( function( mw, $, bs ) {
	bs.util.registerNamespace( 'bs.aggregatedStatistics.charts' );

	bs.aggregatedStatistics.charts.Barchart = function() {
		bs.aggregatedStatistics.charts.Barchart.super.call( this );
		this.width = 600;
		this.height = 300;
	};

	OO.inheritClass( bs.aggregatedStatistics.charts.Barchart, OO.ui.Widget );

	bs.aggregatedStatistics.charts.Barchart.prototype.setAxisLabels = function ( labels  ) {
		this.labels = Object.values( labels );
	};

	bs.aggregatedStatistics.charts.Barchart.prototype.updateData = function ( data ) {
		this.data = data;
		this.viewchart();
	};

	bs.aggregatedStatistics.charts.Barchart.prototype.viewchart = function () {
		xScale = d3.scaleBand()
			.domain( d3.range( this.data.length ) )
			.range( [ 40, this.width ] )
			.padding( 0.5 );

		yScale = d3.scaleLinear()
			.domain( [ 0, d3.max( this.data, d => d.value ) ] ).nice()
			.range( [ this.height - 30, 30 ] )

		this.xAxis = g => g
			.attr( "transform", `translate(0, ${ this.height - 30 } )` )
			.call( d3.axisBottom( xScale ).tickFormat( i => this.data[i].name ).tickSizeOuter( 0 ) )
			.selectAll( "text" )
			.attr( "text-anchor", "end" )
			.attr( "transform", "rotate(-65)" )
			.attr( "font-size", "9px" );

		this.yAxis = g => g
			.attr( "transform", `translate( ${40} , 0 )`)
			.call( d3.axisLeft( yScale ).ticks( null, this.data.format ) );

		this.chart = d3.select( "#chartCtn" )
				.append( "svg" )
				.attr("id", "chart")
				.attr( "viewBox", [ 0, 0, this.width, this.height + 90 ] );

		this.chart.selectAll( "g" )
			.data( this.data )
			.enter()
			.append( "rect" )
			.attr( "x", ( d, i ) => xScale( i ) )
			.attr( "y", d => yScale( d.value ) )
			.attr( "height", d => yScale( 0 ) - yScale( d.value ) )
			.attr( "width", xScale.bandwidth() )
			.attr( "fill", "#eaecf0" )
			.on( 'mouseenter', function ( actual, i ) {
				d3.select(this)
				.attr('opacity', 0.6);

				$tooltip
				.html( i.value )
				.css('visibility', 'visible')
				.css("top", ( actual.y + 90 + 'px'))
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

			this.chart.append( "g" ).call( this.xAxis );

			this.chart.append( "g" ).call( this.yAxis );
			this.chart.append('g')
				.attr("class", "grid")
				.attr( "transform", `translate( ${40} , 0 )`)
				.call(d3.axisLeft( yScale )
				.tickSize( -this.width  , 0, 0)
				.tickFormat(''));

			this.chart.append("text")
				.attr("transform", "rotate(-90)")
				.attr("x", 0 - (this.height / 2) )
				.attr("y",  10 )
				.attr("text-anchor", "middle")
				.style( "font-size", "9px" )
				.text( this.labels[0] );

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
