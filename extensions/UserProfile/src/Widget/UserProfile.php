<?php

namespace MediaWiki\Extension\UserProfile\Widget;

use MediaWiki\Html\Html;
use MediaWiki\Message\Message;
use MediaWiki\Title\Title;
use OOUI\Exception;
use OOUI\HtmlSnippet;
use OOUI\LabelWidget;
use OOUI\PanelLayout;

/**
 * Server-side counterpart to the ext.userProfile.ui.ProfilePanel
 */
class UserProfile extends PanelLayout {
	/** @var PanelLayout */
	private PanelLayout $mainPanel;

	/** @var string */
	private string $userDisplay;

	/**
	 * @param array $config
	 * @throws Exception
	 */
	public function __construct( array $config = [] ) {
		$config['expanded'] = false;
		$config['padded'] = false;
		parent::__construct( $config );
		$this->addClasses( [ 'user-profile', 'rendered' ] );

		$this->userDisplay = $config['userDisplay'] ?? $config['username'];

		$this->mainPanel = new PanelLayout( [
			'classes' => [ 'user-profile-panel' ],
			'expanded' => false,
			'padded' => true,
		] );
		$this->appendContent( $this->mainPanel );
		$this->addImage( $config );
		$this->addData( $config );
	}

	/**
	 * @param array $config
	 * @throws Exception
	 */
	private function addImage( array $config ) {
		$img = Html::element( 'img', [
			'alt' => Message::newFromKey( 'userprofile-profile-image-alt', $this->userDisplay )->text(),
			'src' => $config['imageUrl'] ?? ''
		] );
		$this->mainPanel->appendContent( new HtmlSnippet( Html::rawElement( 'div', [
			'class' => 'user-profile-image-cnt',
		], $img ) ) );
	}

	/**
	 * @param array $config
	 * @throws Exception
	 */
	private function addData( array $config ) {
		$hasRealName = $config['realName'] && strlen( $config['realName'] ) > 0;
		$mainName = $hasRealName ? $config['realName'] : $config['username'];
		$nameHeader = new PanelLayout( [
			'expanded' => false, 'padded' => false,
			'classes' => [ 'user-profile-name-header' ]
		] );
		$nameHeader->appendContent( new HtmlSnippet( Html::element( 'a', [
			'href' => Title::makeTitle( NS_USER, $config['username'] )->getLocalURL(),
			'title' => $hasRealName ?
				Message::newFromKey( 'userprofile-profile-name-title' )->text() :
				Message::newFromKey( 'userprofile-profile-username-title' )->text(),
			'class' => 'user-profile-name'
		], $mainName ) ) );
		if ( $hasRealName && $config['show_username'] ) {
			$nameHeader->appendContent( new LabelWidget( [
				'label' => '(@' . $config['username'] . ')',
				'title' => Message::newFromKey( 'userprofile-profile-username-title' )->text(),
				'classes' => [ 'user-profile-username' ]
			] ) );
		}

		$metaPanel = new PanelLayout( [
			'expanded' => false, 'padded' => false,
			'classes' => [ 'user-profile-meta-cnt' ]
		] );
		$this->renderMeta( $config, $metaPanel );
		$fieldsPanel = new PanelLayout( [
			'expanded' => false, 'padded' => false,
			'classes' => [ 'user-profile-fields-cnt' ]
		] );
		$this->renderFields( $config, $fieldsPanel );

		$dataCnt = new PanelLayout( [
			'expanded' => false, 'padded' => false,
			'classes' => [ 'user-profile-data-cnt' ]
		] );
		$dataCnt->appendContent( $nameHeader );
		$dataCnt->appendContent( $metaPanel );
		$dataCnt->appendContent( $fieldsPanel );

		$this->mainPanel->appendContent( $dataCnt );
	}

	/**
	 * @param array $config
	 * @param PanelLayout $panel
	 * @throws Exception
	 */
	private function renderMeta( array $config, PanelLayout $panel ) {
		$metas = [];
		foreach ( $config['fields'] as $key => $field ) {
			if ( isset( $field['isMeta'] ) && $field['isMeta'] && isset( $config[$key] ) ) {
				$metaPanel = new PanelLayout( [
					'expanded' => false, 'padded' => false,
					'classes' => [ 'user-profile-meta' ]
				] );
				$metaPanel->appendContent( new HtmlSnippet( $field['label'] . ': ' . $config[$key] ) );
				$metas[] = $metaPanel;
			}
		}
		if ( count( $metas ) > 0 ) {
			$panel->appendContent( $metas );
		}
	}

	/**
	 * @param array $config
	 * @param PanelLayout $panel
	 * @throws Exception
	 */
	private function renderFields( array $config, PanelLayout $panel ) {
		$fields = [];
		foreach ( $config['fields'] as $key => $field ) {
			if ( isset( $field['isMeta'] ) && $field['isMeta'] ) {
				continue;
			}
			if ( isset( $field['isSystem'] ) && $field['isSystem'] ) {
				continue;
			}
			if ( !isset( $config[$key] ) || $config[$key] === '' ) {
				continue;
			}
			$valueWidget = new LabelWidget( [
				'label' => $config[$key],
				'classes' => [ 'user-profile-field-value' ]
			] );
			if ( isset( $field['url'] ) ) {
				$valueWidget = new HtmlSnippet( Html::element( 'a', [
					'href' => str_replace( '{value}', $config[$key], $field['url'] ),
					'rel' => 'noopener noreferrer',
					'class' => 'user-profile-field-value'
				], $config[$key] ) );
			}
			$fieldPanel = new PanelLayout( [
				'expanded' => false, 'padded' => false,
				'classes' => [ 'user-profile-field' ]
			] );
			$fieldPanel->appendContent( [
				new LabelWidget( [
					'label' => $field['label'],
					'classes' => [ 'user-profile-field-label' ]
				] ),
				$valueWidget
			] );
			$fields[] = $fieldPanel;
		}
		if ( count( $fields ) > 0 ) {
			$panel->appendContent( $fields );
		}
	}
}
