<?php

/**
 * MySQL-specific installer.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @ingroup Installer
 */

namespace MediaWiki\Installer;

use MediaWiki\Status\Status;
use Wikimedia\Rdbms\Database;
use Wikimedia\Rdbms\DatabaseFactory;
use Wikimedia\Rdbms\DatabaseMySQL;
use Wikimedia\Rdbms\DBConnectionError;
use Wikimedia\Rdbms\DBQueryError;
use Wikimedia\Rdbms\IDatabase;

/**
 * Class for setting up the MediaWiki database using MySQL.
 *
 * @ingroup Installer
 * @since 1.17
 */
class MysqlInstaller extends DatabaseInstaller {

	/** @inheritDoc */
	protected $globalNames = [
		'wgDBserver',
		'wgDBname',
		'wgDBuser',
		'wgDBpassword',
		'wgDBssl',
		'wgDBprefix',
		'wgDBTableOptions',
	];

	/** @inheritDoc */
	protected $internalDefaults = [
		'_MysqlEngine' => 'InnoDB',
		'_MysqlCharset' => 'binary',
		'_InstallUser' => 'root',
	];

	/** @var string[] */
	public $supportedEngines = [ 'InnoDB' ];

	private const MIN_VERSIONS = [
		'MySQL' => '5.7.0',
		'MariaDB' => '10.3',
	];
	/** @inheritDoc */
	public static $minimumVersion;
	/** @inheritDoc */
	protected static $notMinimumVersionMessage;

	/** @var string[] */
	public $webUserPrivs = [
		'DELETE',
		'INSERT',
		'SELECT',
		'UPDATE',
		'CREATE TEMPORARY TABLES',
	];

	/**
	 * @return string
	 */
	public function getName() {
		return 'mysql';
	}

	/**
	 * @return bool
	 */
	public function isCompiled() {
		return self::checkExtension( 'mysqli' );
	}

	public function getConnectForm( WebInstaller $webInstaller ): DatabaseConnectForm {
		return new MysqlConnectForm( $webInstaller, $this );
	}

	public function getSettingsForm( WebInstaller $webInstaller ): DatabaseSettingsForm {
		return new MysqlSettingsForm( $webInstaller, $this );
	}

	public static function meetsMinimumRequirement( IDatabase $conn ) {
		$type = str_contains( $conn->getSoftwareLink(), 'MariaDB' ) ? 'MariaDB' : 'MySQL';
		self::$minimumVersion = self::MIN_VERSIONS[$type];
		// Used messages: config-mysql-old, config-mariadb-old
		self::$notMinimumVersionMessage = 'config-' . strtolower( $type ) . '-old';
		return parent::meetsMinimumRequirement( $conn );
	}

	/**
	 * @param string $type
	 * @return ConnectionStatus
	 */
	protected function openConnection( string $type ) {
		$status = new ConnectionStatus;
		$dbName = $type === DatabaseInstaller::CONN_CREATE_DATABASE
			? null : $this->getVar( 'wgDBname' );
		try {
			/** @var DatabaseMySQL $db */
			$db = ( new DatabaseFactory() )->create( 'mysql', [
				'host' => $this->getVar( 'wgDBserver' ),
				'user' => $this->getVar( '_InstallUser' ),
				'password' => $this->getVar( '_InstallPassword' ),
				'ssl' => $this->getVar( 'wgDBssl' ),
				'dbname' => $dbName,
				'flags' => 0,
				'tablePrefix' => $this->getVar( 'wgDBprefix' ) ] );
			$status->setDB( $db );
		} catch ( DBConnectionError $e ) {
			$status->fatal( 'config-connection-error', $e->getMessage() );
		}

		return $status;
	}

	public function preUpgrade() {
		global $wgDBuser, $wgDBpassword;

		$status = $this->getConnection( self::CONN_CREATE_TABLES );
		if ( !$status->isOK() ) {
			$this->parent->showStatusMessage( $status );

			return;
		}
		$conn = $status->getDB();
		# Determine existing default character set
		if ( $conn->tableExists( "revision", __METHOD__ ) ) {
			$revision = $this->escapeLikeInternal( $this->getVar( 'wgDBprefix' ) . 'revision', '\\' );
			$res = $conn->query( "SHOW TABLE STATUS LIKE '$revision'", __METHOD__ );
			$row = $res->fetchObject();
			if ( !$row ) {
				$this->parent->showMessage( 'config-show-table-status' );
				$existingSchema = false;
				$existingEngine = false;
			} else {
				if ( preg_match( '/^latin1/', $row->Collation ) ) {
					$existingSchema = 'latin1';
				} elseif ( preg_match( '/^utf8/', $row->Collation ) ) {
					$existingSchema = 'utf8';
				} elseif ( preg_match( '/^binary/', $row->Collation ) ) {
					$existingSchema = 'binary';
				} else {
					$existingSchema = false;
					$this->parent->showMessage( 'config-unknown-collation' );
				}
				$existingEngine = $row->Engine ?? $row->Type;
			}
		} else {
			$existingSchema = false;
			$existingEngine = false;
		}

		if ( $existingSchema && $existingSchema != $this->getVar( '_MysqlCharset' ) ) {
			$this->setVar( '_MysqlCharset', $existingSchema );
		}
		if ( $existingEngine && $existingEngine != $this->getVar( '_MysqlEngine' ) ) {
			$this->setVar( '_MysqlEngine', $existingEngine );
		}

		# Normal user and password are selected after this step, so for now
		# just copy these two
		$wgDBuser = $this->getVar( '_InstallUser' );
		$wgDBpassword = $this->getVar( '_InstallPassword' );
	}

	/**
	 * @param string $s
	 * @param string $escapeChar
	 * @return string
	 */
	protected function escapeLikeInternal( $s, $escapeChar = '`' ) {
		return str_replace( [ $escapeChar, '%', '_' ],
			[ "{$escapeChar}{$escapeChar}", "{$escapeChar}%", "{$escapeChar}_" ],
			$s );
	}

	/**
	 * Get a list of storage engines that are available and supported
	 *
	 * @return array
	 */
	public function getEngines() {
		$status = $this->getConnection( self::CONN_CREATE_DATABASE );
		$conn = $status->getDB();

		$engines = [];
		$res = $conn->query( 'SHOW ENGINES', __METHOD__ );
		foreach ( $res as $row ) {
			if ( $row->Support == 'YES' || $row->Support == 'DEFAULT' ) {
				$engines[] = $row->Engine;
			}
		}
		$engines = array_intersect( $this->supportedEngines, $engines );

		return $engines;
	}

	/**
	 * Get a list of character sets that are available and supported
	 *
	 * @return array
	 */
	public function getCharsets() {
		return [ 'binary', 'utf8' ];
	}

	/**
	 * Return true if the install user can create accounts
	 *
	 * @return bool
	 */
	public function canCreateAccounts() {
		$status = $this->getConnection( self::CONN_CREATE_DATABASE );
		if ( !$status->isOK() ) {
			return false;
		}
		$conn = $status->getDB();

		// Get current account name
		$currentName = $conn->selectField( '', 'CURRENT_USER()', '', __METHOD__ );
		$parts = explode( '@', $currentName );
		if ( count( $parts ) != 2 ) {
			return false;
		}
		$quotedUser = $conn->addQuotes( $parts[0] ) .
			'@' . $conn->addQuotes( $parts[1] );

		// The user needs to have INSERT on mysql.* or (global) CREATE USER to be able to CREATE USER
		// The grantee will be double-quoted in this query, as required
		$res = $conn->select( 'INFORMATION_SCHEMA.USER_PRIVILEGES', '*',
			[ 'GRANTEE' => $quotedUser ], __METHOD__ );
		$insertMysql = false;
		// The user needs to have permission to GRANT all necessary permissions to the newly created user.
		// This starts as a list of all necessary permissions and they are removed as they are found.
		// If any are left in the list after checking for permissions, those are the missing permissions.
		$missingGrantOptions = array_fill_keys( $this->webUserPrivs, true );
		foreach ( $res as $row ) {
			if ( $row->PRIVILEGE_TYPE == 'INSERT' ) {
				$insertMysql = true;
			} elseif ( $row->PRIVILEGE_TYPE == 'CREATE USER' ) {
				$insertMysql = true;
			}
			if ( $row->IS_GRANTABLE === 'YES' ) {
				unset( $missingGrantOptions[$row->PRIVILEGE_TYPE] );
			}
		}

		// Check for DB-specific privs for mysql.*
		if ( !$insertMysql ) {
			$row = $conn->selectRow( 'INFORMATION_SCHEMA.SCHEMA_PRIVILEGES', '*',
				[
					'GRANTEE' => $quotedUser,
					'TABLE_SCHEMA' => 'mysql',
					'PRIVILEGE_TYPE' => 'INSERT',
				], __METHOD__ );
			if ( $row ) {
				$insertMysql = true;
			}
		}

		if ( !$insertMysql ) {
			return false;
		}

		// Check for DB-level grant options
		$res = $conn->select( 'INFORMATION_SCHEMA.SCHEMA_PRIVILEGES', '*',
			[
				'GRANTEE' => $quotedUser,
				'IS_GRANTABLE' => 'YES',
			], __METHOD__ );
		foreach ( $res as $row ) {
			$regex = $this->likeToRegex( $row->TABLE_SCHEMA );
			if ( preg_match( $regex, $this->getVar( 'wgDBname' ) ) ) {
				unset( $missingGrantOptions[$row->PRIVILEGE_TYPE] );
			}
		}
		if ( count( $missingGrantOptions ) ) {
			// Can't grant everything
			return false;
		}

		return true;
	}

	/**
	 * Convert a wildcard (as used in LIKE) to a regex
	 * Slashes are escaped, slash terminators included
	 * @param string $wildcard
	 * @return string
	 */
	protected function likeToRegex( $wildcard ) {
		$r = preg_quote( $wildcard, '/' );
		$r = strtr( $r, [
			'%' => '.*',
			'_' => '.'
		] );
		return "/$r/s";
	}

	public function preInstall() {
		# Add our user callback to installSteps, right before the tables are created.
		$callback = [
			'name' => 'user',
			'callback' => [ $this, 'setupUser' ],
		];
		$this->parent->addInstallStep( $callback, 'tables' );
	}

	/**
	 * @return Status
	 */
	public function setupDatabase() {
		$status = $this->getConnection( self::CONN_CREATE_DATABASE );
		if ( !$status->isOK() ) {
			return $status;
		}
		$conn = $status->getDB();
		$dbName = $this->getVar( 'wgDBname' );
		if ( !$this->databaseExists( $dbName ) ) {
			$conn->query(
				"CREATE DATABASE " . $conn->addIdentifierQuotes( $dbName ) . "CHARACTER SET utf8",
				__METHOD__
			);
		}
		$this->selectDatabase( $conn, $dbName );

		return $status;
	}

	/**
	 * Try to see if a given database exists
	 * @param string $dbName Database name to check
	 * @return bool
	 */
	private function databaseExists( $dbName ) {
		return (bool)$this->definitelyGetConnection( self::CONN_CREATE_DATABASE )
			->newSelectQueryBuilder()
			->select( '1' )
			->from( 'information_schema.schemata' )
			->where( [ 'schema_name' => $dbName ] )
			->fetchRow();
	}

	/**
	 * @return Status
	 */
	public function setupUser() {
		$dbUser = $this->getVar( 'wgDBuser' );
		if ( $dbUser == $this->getVar( '_InstallUser' ) ) {
			return Status::newGood();
		}
		$status = $this->getConnection( self::CONN_CREATE_DATABASE );
		if ( !$status->isOK() ) {
			return $status;
		}

		$conn = $status->getDB();
		$server = $this->getVar( 'wgDBserver' );
		$password = $this->getVar( 'wgDBpassword' );
		$grantableNames = [];

		if ( $this->getVar( '_CreateDBAccount' ) ) {
			// Before we blindly try to create a user that already has access,
			try { // first attempt to connect to the database
				( new DatabaseFactory() )->create( 'mysql', [
					'host' => $server,
					'user' => $dbUser,
					'password' => $password,
					'ssl' => $this->getVar( 'wgDBssl' ),
					'dbname' => null,
					'flags' => 0,
					'tablePrefix' => $this->getVar( 'wgDBprefix' )
				] );
				$grantableNames[] = $this->buildFullUserName( $conn, $dbUser, $server );
				$tryToCreate = false;
			} catch ( DBConnectionError $e ) {
				$tryToCreate = true;
			}
		} else {
			$grantableNames[] = $this->buildFullUserName( $conn, $dbUser, $server );
			$tryToCreate = false;
		}

		if ( $tryToCreate ) {
			$createHostList = [
				$server,
				'localhost',
				'localhost.localdomain',
				'%'
			];

			$createHostList = array_unique( $createHostList );
			$escPass = $conn->addQuotes( $password );

			foreach ( $createHostList as $host ) {
				$fullName = $this->buildFullUserName( $conn, $dbUser, $host );
				if ( !$this->userDefinitelyExists( $conn, $host, $dbUser ) ) {
					try {
						$conn->begin( __METHOD__ );
						$conn->query( "CREATE USER $fullName IDENTIFIED BY $escPass", __METHOD__ );
						$conn->commit( __METHOD__ );
						$grantableNames[] = $fullName;
					} catch ( DBQueryError $dqe ) {
						if ( $conn->lastErrno() == 1396 /* ER_CANNOT_USER */ ) {
							// User (probably) already exists
							$conn->rollback( __METHOD__ );
							$status->warning( 'config-install-user-alreadyexists', $dbUser );
							$grantableNames[] = $fullName;
							break;
						} else {
							// If we couldn't create for some bizarre reason and the
							// user probably doesn't exist, skip the grant
							$conn->rollback( __METHOD__ );
							$status->warning( 'config-install-user-create-failed', $dbUser, $dqe->getMessage() );
						}
					}
				} else {
					$status->warning( 'config-install-user-alreadyexists', $dbUser );
					$grantableNames[] = $fullName;
					break;
				}
			}
		}

		// Try to grant to all the users we know exist or we were able to create
		$dbAllTables = $conn->addIdentifierQuotes( $this->getVar( 'wgDBname' ) ) . '.*';
		foreach ( $grantableNames as $name ) {
			try {
				$conn->begin( __METHOD__ );
				$conn->query( "GRANT ALL PRIVILEGES ON $dbAllTables TO $name", __METHOD__ );
				$conn->commit( __METHOD__ );
			} catch ( DBQueryError $dqe ) {
				$conn->rollback( __METHOD__ );
				$status->fatal( 'config-install-user-grant-failed', $dbUser, $dqe->getMessage() );
			}
		}

		return $status;
	}

	/**
	 * Return a formal 'User'@'Host' username for use in queries
	 * @param Database $conn
	 * @param string $name Username, quotes will be added
	 * @param string $host Hostname, quotes will be added
	 * @return string
	 */
	private function buildFullUserName( $conn, $name, $host ) {
		return $conn->addQuotes( $name ) . '@' . $conn->addQuotes( $host );
	}

	/**
	 * Try to see if the user account exists. Our "superuser" may not have
	 * access to mysql.user, so false means "no" or "maybe"
	 * @param Database $conn
	 * @param string $host Hostname to check
	 * @param string $user Username to check
	 * @return bool
	 */
	private function userDefinitelyExists( $conn, $host, $user ) {
		try {
			$res = $conn->newSelectQueryBuilder()
				->select( [ 'Host', 'User' ] )
				->from( 'mysql.user' )
				->where( [ 'Host' => $host, 'User' => $user ] )
				->caller( __METHOD__ )->fetchRow();

			return (bool)$res;
		} catch ( DBQueryError $dqe ) {
			return false;
		}
	}

	/**
	 * Return any table options to be applied to all tables that don't
	 * override them.
	 *
	 * @return string
	 */
	protected function getTableOptions() {
		$options = [];
		if ( $this->getVar( '_MysqlEngine' ) !== null ) {
			$options[] = "ENGINE=" . $this->getVar( '_MysqlEngine' );
		}
		if ( $this->getVar( '_MysqlCharset' ) !== null ) {
			$options[] = 'DEFAULT CHARSET=' . $this->getVar( '_MysqlCharset' );
		}

		return implode( ', ', $options );
	}

	/**
	 * Get variables to substitute into tables.sql and the SQL patch files.
	 *
	 * @return array
	 */
	public function getSchemaVars() {
		return [
			'wgDBTableOptions' => $this->getTableOptions(),
			'wgDBname' => $this->getVar( 'wgDBname' ),
			'wgDBuser' => $this->getVar( 'wgDBuser' ),
			'wgDBpassword' => $this->getVar( 'wgDBpassword' ),
		];
	}

	public function getLocalSettings() {
		$prefix = LocalSettingsGenerator::escapePhpString( $this->getVar( 'wgDBprefix' ) );
		$useSsl = $this->getVar( 'wgDBssl' ) ? 'true' : 'false';
		$tblOpts = LocalSettingsGenerator::escapePhpString( $this->getTableOptions() );

		return "# MySQL specific settings
\$wgDBprefix = \"{$prefix}\";
\$wgDBssl = {$useSsl};

# MySQL table options to use during installation or update
\$wgDBTableOptions = \"{$tblOpts}\";";
	}
}
