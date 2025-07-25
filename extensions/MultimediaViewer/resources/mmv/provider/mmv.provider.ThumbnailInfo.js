/*
 * This file is part of the MediaWiki extension MultimediaViewer.
 *
 * MultimediaViewer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * MultimediaViewer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MultimediaViewer.  If not, see <http://www.gnu.org/licenses/>.
 */

const Api = require( './mmv.provider.Api.js' );
const Thumbnail = require( '../model/mmv.model.Thumbnail.js' );

/**
 * Gets thumbnail information.
 *
 * See https://www.mediawiki.org/wiki/API:Properties#imageinfo_.2F_ii
 */
class ThumbnailInfo extends Api {
	/**
	 * @param {mw.Api} api
	 * @param {Object} [options]
	 * @param {number} [options.maxage] cache expiration time, in seconds
	 *  Will be used for both client-side cache (maxage) and reverse proxies (s-maxage)
	 */
	constructor( api, options ) {
		super( api, options );
	}

	/**
	 * Runs an API GET request to get the thumbnail info for the specified size.
	 * The thumbnail always has the same aspect ratio as the full image.
	 * One of width or height can be null; if both are set, the API will return the largest
	 * thumbnail which fits into a width x height bounding box (or the full-sized image - whichever
	 * is smaller).
	 *
	 * @param {mw.Title} file
	 * @param {string} sampleUrl a thumbnail URL for the same file (but with different size).
	 * @param {number} width thumbnail width in pixels
	 * @param {number} height thumbnail height in pixels
	 * @return {jQuery.Promise.<Thumbnail>}
	 */
	get( file, sampleUrl, width, height ) {
		const match = sampleUrl.match( /(lang|page)([\d\-a-z]+)-(\d+)px/ ); // multi lingual SVG or PDF page
		const iiurlparam = match ? `${ match[ 1 ] }${ match[ 2 ] }-${ width }px` : undefined;
		const cacheKey = [ file.getPrefixedDb(), width || '', height || '', iiurlparam || '', file.timestamp || '' ].join();

		const params = {
			formatversion: 2,
			action: 'query',
			prop: 'imageinfo',
			titles: file.getPrefixedDb(),
			iiprop: 'url',
			iiurlparam,
			iiurlwidth: width,
			iiurlheight: height
		};

		if ( file.timestamp ) {
			params.iistart = file.timestamp - 1;
		}

		return this.getCachedPromise( cacheKey, () => this.apiGetWithMaxAge(
			params
		).then( ( data ) => this.getQueryPage( data ) ).then( ( page ) => {
			let imageInfo;
			if ( page.imageinfo && page.imageinfo[ 0 ] ) {
				imageInfo = page.imageinfo[ 0 ];
				if ( imageInfo.thumburl && imageInfo.thumbwidth && imageInfo.thumbheight ) {
					return $.Deferred().resolve(
						new Thumbnail(
							imageInfo.thumburl,
							imageInfo.thumbwidth,
							imageInfo.thumbheight
						)
					);
				} else {
					return $.Deferred().reject( 'error in provider, thumb info not found' );
				}
			} else if ( page.missing === true && page.imagerepository === '' ) {
				return $.Deferred().reject( `file does not exist: ${ file.getPrefixedDb() }` );
			} else {
				return $.Deferred().reject( 'unknown error' );
			}
		} ) );
	}
}

module.exports = ThumbnailInfo;
