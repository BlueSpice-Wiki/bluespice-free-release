( function( mw, $, bs ) {
	bs.util.registerNamespace( 'bs.aggregatedStatistics.charts' );

	bs.aggregatedStatistics.charts.Bubblechart = function() {
		bs.aggregatedStatistics.charts.Bubblechart.super.call( this );
		this.width = 600;
		this.height = 300;
	};

	OO.inheritClass( bs.aggregatedStatistics.charts.Bubblechart, OO.ui.Widget );

	bs.aggregatedStatistics.charts.Bubblechart.prototype.setAxisLabels = function ( labels  ) {
		this.labels = Object.values( labels );
	};

	bs.aggregatedStatistics.charts.Bubblechart.prototype.updateData = function ( data ) {
		this.data = data;
		this.data = d3.pack()
		.size( [ this.width - 2, this.height - 2 ] )
		.padding(3)
		(d3.hierarchy({children: this.data } ) .sum( d => d.value ) )
		this.viewchart();
	};

	bs.aggregatedStatistics.charts.Bubblechart.prototype.viewchart = function () {

		this.format = d3.format(",d");
		this.color = d3.scaleOrdinal()
			.range( [ "#98abc5", "#8a89a6", "#7b6888", "#6b486b", "#a05d56", "#d0743c", "#ff8c00" ] );

		this.chart = d3.create( "svg" )
				.append( "svg" )
				.attr( "viewBox", [ 0, 0, this.width, this.height ] )
				.attr( "text-anchor", "middle" );

		this.chart.selectAll( "g" )
				.data( this.data.leaves() )
				.enter()
				.append( "circle" )
				.attr("transform", d => `translate(${d.x + 1},${d.y + 1})`)
				.attr( "r", ( d ) => d.r )
				.attr( "fill",  d => this.color( d.data.name ) );

		this.chart.append( "text" )
			.selectAll( "tspan" )
			.data( this.data, function (d) {return d.data.name} )
			.join( "tspan" )
			.attr( "x", function ( d ) { return d.x })
			.attr( "y", function ( d ) {return d.y })
			.attr( "font-size", "9px" )
			.attr( "text-anchor", "middle" )
			.text( function ( d ) { return d.data.name; } );

		this.chart.append("title")
		.text(this.data, function(d) { `${d.data.name === undefined ? "" : `${d.data.name}
		`}${format(d.value)}`});

		this.$element =  this.chart;
	};

} )( mediaWiki, jQuery, blueSpice );
