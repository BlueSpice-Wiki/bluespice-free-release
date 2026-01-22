'use strict';

mws.wire.Message = class WireMessage {
	constructor( channel, payload = null ) {
		this.channel = channel;
		this.payload = payload;
	}

	toJSON() {
		return {
			channel: this.channel,
			payload: this.payload
		};
	}

	static fromData( data ) {
		if ( typeof data === 'string' ) {
			data = JSON.parse( data );
		}
		if ( !data || !data.channel || !data.payload ) {
			throw new Error( 'Invalid data format for WireMessage' );
		}
		return new WireMessage( data.channel, data.payload );
	}
};
