In the `LocalSettings.php` add

```php
// Spec
$GLOBALS['mwsgCommonUISkinSlots']['skinAfterContent']['my-skinaftercontent-panel'] = [
	'factory' => function() {
		return new MWStake\MediaWiki\Component\CommonUserInterface\Component\SimplePanel( [
			'id' => 'my-panel',
			'label' => new RawMessage( 'My panel' ),
			'items' => [
				new MWStake\MediaWiki\Component\CommonUserInterface\Component\Literal( 'my-literal', 'Hello World!'),
				new MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleButton( [
					'id' => 'my-button',
					'label' => new RawMessage( 'My Button' )
				] ),
				new MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleDropdown( [
					'id' => 'my-first-dropdown',
					'aria-label' => new RawMessage( 'my aria label' ),
					'aria-desc' => new RawMessage( 'my aria desc' ),
					'items' => [
						new MWStake\MediaWiki\Component\CommonUserInterface\Component\DropdownItemlist(
							'my-dd', [
								'link1' => [
									'id' => 'link-1',
									'text' => 'Test 1',
									'title' => 'Klick mich',
									'href' => 'https://google.de',
									'class' => 'class1 class2',
								],
								'link2' => [
									'id' => 'link-2',
									'text' => 'Test 2',
									'title' => 'Klick mich',
									'href' => 'https://google.de',
									'class' => 'class1 class2',
								]
							]
						),
						new MWStake\MediaWiki\Component\CommonUserInterface\Component\Separator( 'primary' ),
						new MWStake\MediaWiki\Component\CommonUserInterface\Component\DropdownItemlist(
							'my-dd-2', [
								'link1' => [
									'id' => 'link-1',
									'text' => 'Test 1',
									'title' => 'Klick mich',
									'href' => 'https://google.de',
									'class' => 'class1 class2',
								],
								'link2' => [
									'id' => 'link-2',
									'text' => 'Test 2',
									'title' => 'Klick mich',
									'href' => 'https://google.de',
									'class' => 'class1 class2',
								]
							]
						)
					]
				] ),
				new MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleCard( [
					'id' => 'my-SimpleCard',
					'items' => [
						new MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleCardHeader( [
							'classes' => [ 'my-SimpleCard-header' ],
							'items' => [
								new MWStake\MediaWiki\Component\CommonUserInterface\Component\Literal( 'my-header', 'Hello World!')
							]
						] ),
						new MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleCardBody( [
							'classes' => [ 'my-SimpleCard-body' ],
							'items' => [
									new MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleCardTitle( [
								'classes' => [ 'my-SimpleCard-Title' ],
								'items' => [
									new MWStake\MediaWiki\Component\CommonUserInterface\Component\Literal( 'my-title', 'Hello World!')
								]
								] ),
								new MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleCardSubTitle( [
									'classes' => [ 'my-SimpleCard-subtitle' ],
									'items' => [
										new MWStake\MediaWiki\Component\CommonUserInterface\Component\Literal( 'my-subtitle', 'Hello World!')
									]
								] ),
								new MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleCardText( [
									'classes' => [ 'my-SimpleCard-text'] ,
									'items' => [
										new MWStake\MediaWiki\Component\CommonUserInterface\Component\Literal( 'my-text', 'Hello World!')
									]
								] ),
							]
						] ),
						new MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleCardFooter( [
							'classes' => [ 'my-SimpleCard-footer' ],
							'items' => [
								new MWStake\MediaWiki\Component\CommonUserInterface\Component\Literal( 'my-footer', 'Hello World!')
							]
						] ),
					]
				] )
			]
		] );
	}
];
```