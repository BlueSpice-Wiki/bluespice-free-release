( function ( mw, $, bs ) {
	bs.util.registerNamespace( 'bs.pageAssignments.report' );

	bs.pageAssignments.report.AssignedPagesReport = function ( cfg ) {
		bs.pageAssignments.report.AssignedPagesReport.parent.call( this, cfg );
	};

	OO.inheritClass( bs.pageAssignments.report.AssignedPagesReport, bs.aggregatedStatistics.report.ReportBase );

	bs.pageAssignments.report.AssignedPagesReport.static.label = mw.message( 'bs-pageassignments-statistics-report-assigned-pages' ).text();

	bs.pageAssignments.report.AssignedPagesReport.prototype.getFilters = function () {
		return [
			new bs.aggregatedStatistics.filter.NamespaceCategoryFilter( { onlyContentNamespaces: false } )
		];
	};

	bs.pageAssignments.report.AssignedPagesReport.prototype.getChart = function () {
		return new bs.aggregatedStatistics.charts.Groupchart();
	};

	bs.pageAssignments.report.AssignedPagesReport.prototype.getAxisLabels = function () {
		return {
			value: mw.message( 'bs-pageassignments-statistics-report-assigned-pages-axis-label' ).text(),
			assigned: mw.message( 'bs-pageassignments-statistics-report-assigned-pages-axis-assigned' ).text(),
			unassigned: mw.message( 'bs-pageassignments-statistics-report-assigned-pages-axis-unassigned' ).text()
		};
	};

}( mediaWiki, jQuery, blueSpice ) );
