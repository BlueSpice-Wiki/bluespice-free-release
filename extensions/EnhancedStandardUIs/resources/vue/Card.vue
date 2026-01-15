<template>
	<div id="enhanced-files-tiles-cnt" class="enhanced-files-overview">
		<div class="enhanced-files-tiles">
			<cdx-card
				v-for="card in cards"
				:key="card.id"
				class="enhanced-files-file-card"
				:thumbnail="card.thumbnail"
				tabindex="0"
				@click="onImageClick( $event, card )"
				@keyup.enter="onImageClick( $event, card )">
				<template #title>
					{{ card.title }}
					<cdx-button
						weight="quiet"
						aria-label="Copy link to clipboard"
						:data="card.url"
						@click="onShareClick( $event, card.url )">
						<cdx-icon :icon="cdxIconCopy" size="medium"></cdx-icon>
					</cdx-button>
				</template>
			</cdx-card>
		</div>
	</div>
</template>

<script>
/* eslint-disable */
const { defineComponent } = require( 'vue' ),
	{ CdxCard, CdxIcon, CdxButton } = require( '@wikimedia/codex' ),
	{ cdxIconCopy } = require( './icons.json' );

// @vue/component
module.exports = defineComponent( {
	name: 'FileCard',
	components: { CdxCard,
		CdxButton,
		CdxIcon
	},
	props: {
		cards: {
			type: Array,
			default: []
		}
	},
	setup() {
		return {
			cdxIconCopy
		};
	},
	methods: {
		onShareClick( event, fileUrl ) {
			navigator.clipboard.writeText( fileUrl );
			mw.notify( mw.message( 'enhanced-standard-uis-filelist-notify-copy-clipboard' ).text() );
			event.stopPropagation();
		},
		onImageClick( event, card ) {
			if ( event.target.classList.contains( 'cdx-button') ) {
				return;
			}
			var windowManager = new OO.ui.WindowManager();
			$( document.body ).append( windowManager.$element );
			var infoDialog = new ext.enhancedUI.dialog.FileInfoDialog( {
				data: card,
				page: 'Preview'
			} );
			windowManager.addWindows( [ infoDialog ] );
			windowManager.openWindow( infoDialog );
		}
	}
} );
</script>

<style lang="less">
.enhanced-files-tiles {
	display: flex;
	flex-flow: row wrap;
	margin-left: 0;

	> span {
		width: 30%;
		margin: 1rem;
		display: block;
		text-align: center;
		border-color: transparent;
		background-color: transparent;

		&:hover {
			background-color: #fff;
		}

		&:focus-visible {
			background-color: #fff;
			border: 1px solid #36c;
		}

		.cdx-card__thumbnail.cdx-thumbnail {
			.cdx-thumbnail__image {
				width: 120px;
				height: 120px;
				border-color: transparent;
				background-size: auto;
			}
		}

		.cdx-card__text__title > button {
			border-color: transparent;
		}
	}
}

</style>
