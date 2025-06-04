CREATE TABLE IF NOT EXISTS /*$wgDBprefix*/bs_extendedstatistics_snapshot (
    ess_type VARCHAR(255) NOT NULL,
    ess_interval VARCHAR (255) NOT NULL,
	ess_data LONGBLOB NOT NULL,
	ess_secondary_data LONGBLOB NOT NULL,
	ess_timestamp  binary(14)
) /*$wgDBTableOptions*/ COMMENT='BlueSpice: ExtendedStatistics - Stores snapshot data';