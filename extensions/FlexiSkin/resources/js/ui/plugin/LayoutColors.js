flexiskin.ui.plugin.LayoutColors = function () {
	flexiskin.ui.plugin.LayoutColors.parent.call( this );
};

OO.inheritClass( flexiskin.ui.plugin.LayoutColors, flexiskin.ui.plugin.Plugin );

flexiskin.registry.Plugin.register( 'layoutColors', flexiskin.ui.plugin.LayoutColors );

flexiskin.ui.plugin.LayoutColors.prototype.provideControls = function () {
	return {
		color_settings: {
			label: mw.message( 'flexiskin-ui-plugin-color-settings-label' ).text(),
			navbar_colors: {
				label: mw.message( 'flexiskin-ui-plugin-navbar-colors-label' ).text(),
				items: {
					background: {
						label: mw.message( 'flexiskin-ui-plugin-colors-background-navbar-label' ).text(),
						widget: new flexiskin.ui.widget.ColorPicker()
					},
					foreground: {
						label: mw.message( 'flexiskin-ui-plugin-colors-foreground-navbar-label' ).text(),
						widget: new flexiskin.ui.widget.ColorPicker()
					},
					highlight: {
						label: mw.message( 'flexiskin-ui-plugin-colors-highlight-navbar-label' ).text(),
						widget: new flexiskin.ui.widget.ColorPicker()
					}
				}
			},
			sidebar_colors: {
				label: mw.message( 'flexiskin-ui-plugin-sidebar-primary-colors-label' ).text(),
				items: {
					background: {
						label: mw.message( 'flexiskin-ui-plugin-colors-background-sidebar-primary-label' ).text(),
						widget: new flexiskin.ui.widget.ColorPicker()
					},
					foreground: {
						label: mw.message( 'flexiskin-ui-plugin-colors-foreground-sidebar-primary-label' ).text(),
						widget: new flexiskin.ui.widget.ColorPicker()
					},
					highlight: {
						label: mw.message( 'flexiskin-ui-plugin-colors-highlight-sidebar-primary-label' ).text(),
						widget: new flexiskin.ui.widget.ColorPicker()
					}
				}
			},
			footer_colors: {
				label: mw.message( 'flexiskin-ui-plugin-footer-colors-label' ).text(),
				items: {
					background: {
						label: mw.message( 'flexiskin-ui-plugin-colors-background-footer-label' ).text(),
						widget: new flexiskin.ui.widget.ColorPicker()
					},
					foreground: {
						label: mw.message( 'flexiskin-ui-plugin-colors-foreground-footer-label' ).text(),
						widget: new flexiskin.ui.widget.ColorPicker()
					}
				}
			}
		}
	};
};
