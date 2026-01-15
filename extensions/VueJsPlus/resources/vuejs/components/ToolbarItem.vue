<template>
	<li>
		<cdx-button
			:class="spec.class"
			:action="spec.action"
			:weight="spec.weight"
			size="large"
			@click="buttonClick"
		>
			{{ spec.label }}
		</cdx-button>
	</li>
</template>

<script>

const { CdxButton } = require( '@wikimedia/codex' );

// @vue/component
module.exports = exports = {
	name: 'ToolbarItem',
	components: {
		CdxButton
	},
	props: {
		spec: {
			type: Array,
			default: []
		}
	},
	emits: [
		'itemclick'
	],
	data: function () {
		return {
			spec: this.spec
		};
	},
	methods: {
		buttonClick: function () {
			this.$emit( 'itemclick', this.spec );
		}
	}
};

</script>
