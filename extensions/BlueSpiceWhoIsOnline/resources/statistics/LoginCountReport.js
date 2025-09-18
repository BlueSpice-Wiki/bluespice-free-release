( function ( mw, $, bs ) {
	bs.util.registerNamespace( 'bs.whoisonline.report' );

	bs.whoisonline.report.LoginCountReport = function ( cfg ) {
		bs.whoisonline.report.LoginCountReport.parent.call( this, cfg );
	};

	OO.inheritClass( bs.whoisonline.report.LoginCountReport, bs.aggregatedStatistics.report.ReportBase );

	bs.whoisonline.report.LoginCountReport.static.label = mw.message( 'bs-whoisonline-statistics-report-login-number' ).text();

	bs.whoisonline.report.LoginCountReport.static.desc = mw.message( 'bs-whoisonline-statistics-report-login-number-desc' ).text();

	bs.whoisonline.report.LoginCountReport.prototype.getFilters = function () {
		return [];
	};

	bs.whoisonline.report.LoginCountReport.prototype.getChart = function () {
		return new bs.aggregatedStatistics.charts.LineChart();
	};

	bs.whoisonline.report.LoginCountReport.prototype.getAxisLabels = function () {
		return {
			value: mw.message( 'bs-whoisonline-statistics-report-login-number-axis-value' ).text()
		};
	};

}( mediaWiki, jQuery, blueSpice ) );
