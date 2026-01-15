/**
 * Serves as a wrapper for all cookies used in BlueSpiceDiscovery.
 * This is done to encapsulate all the cookies with dynamic names
 * into a single cookie, while keeping the flexibility of the current implementation
 *
 * @constructor
 */
var CookieHandler = function () { // eslint-disable-line no-implicit-globals, no-var
	// This name has to be kept in sync with \BlueSpice\Discovery\CookieHandler::$cookieName
	this.cookieName = 'BlueSpiceDiscovery';
};

/**
 * Get a single cookie value from the unified cookie
 *
 * @param {string} name
 * @return {null|*}
 */
CookieHandler.prototype.get = function ( name ) {
	const parsed = this.parse();
	if ( parsed.hasOwnProperty( name ) ) {
		return parsed[ name ];
	}

	return null;
};

/**
 * Set value of a single cookie that is a part of the unified cookie
 *
 * @param {string} name
 * @param {*} value
 */
CookieHandler.prototype.set = function ( name, value ) {
	const parsed = this.parse();
	parsed[ name ] = value;

	this.setInternally( parsed );
};

/**
 * Combine all single-cookie values and store then into the unified cookie
 *
 * @param {*} values
 */
CookieHandler.prototype.setInternally = function ( values ) {
	mw.cookie.set( this.cookieName, JSON.stringify( values ) );
};

/**
 * Get single-cookie values from the unified cookie
 *
 * @return {Array}
 */
CookieHandler.prototype.parse = function () {
	const value = mw.cookie.get( this.cookieName );
	if ( !value ) {
		return {};
	}

	return JSON.parse( value );
};

( function () {
	discovery_cookie = new CookieHandler();
}() );
