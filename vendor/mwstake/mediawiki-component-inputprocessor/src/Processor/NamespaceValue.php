<?php

namespace MWStake\MediaWiki\Component\InputProcessor\Processor;

use Language;
use MWStake\MediaWiki\Component\InputProcessor\GenericProcessor;
use NamespaceInfo;
use StatusValue;

class NamespaceValue extends GenericProcessor {

	/** @var NamespaceInfo */
	protected NamespaceInfo $namespaceInfo;

	/** @var Language */
	protected Language $language;

	/** @var bool */
	protected bool $mustBeContent = false;

	/** @var bool */
	protected bool $mustBeSubject = false;

	/** @var bool */
	protected bool $mustBeTalk = false;

	/**
	 * @param NamespaceInfo $namespaceInfo
	 * @param Language $language
	 */
	public function __construct( NamespaceInfo $namespaceInfo, Language $language ) {
		$this->namespaceInfo = $namespaceInfo;
		$this->language = $language;
	}

	/**
	 * @param array $spec
	 * @return $this
	 */
	public function initializeFromSpec( array $spec ): static {
		parent::initializeFromSpec( $spec );
		$this->setMustBeContent( $spec['mustBeContent'] ?? false );
		$this->mustBeSubject( $spec['mustBeSubject'] ?? false );
		$this->mustBeTalk( $spec['mustBeTalk'] ?? false );
		return $this;
	}

	/**
	 * @param bool $mustBeContent
	 * @return $this
	 */
	public function setMustBeContent( bool $mustBeContent ): static {
		$this->mustBeContent = $mustBeContent;
		return $this;
	}

	/**
	 * @param bool $mustBeSubject
	 * @return $this
	 */
	public function mustBeSubject( bool $mustBeSubject ): static {
		$this->mustBeSubject = $mustBeSubject;
		return $this;
	}

	/**
	 * @param bool $mustBeTalk
	 * @return $this
	 */
	public function mustBeTalk( bool $mustBeTalk ): static {
		$this->mustBeTalk = $mustBeTalk;
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function process( mixed $value, string $fieldKey ): StatusValue {
		$parentStatus = parent::process( $value, $fieldKey );
		if ( !$parentStatus->isGood() ) {
			return $parentStatus;
		}
		$finalNsId = null;
		$valid = $this->namespaceInfo->getValidNamespaces();
		if ( is_numeric( $value ) ) {
			$ns = (int)$value;
			if ( in_array( $ns, $valid ) ) {
				$finalNsId = $ns;
			}
		} elseif ( is_string( $value ) ) {
			if ( strpos( strtoupper( $value ), 'NS_' ) === 0 ) {
				$ns = constant( $value );
				if ( in_array( $ns, $valid ) ) {
					$finalNsId = $ns;
				}
			} else {
				$ns = $this->namespaceInfo->getCanonicalIndex( $value );
				if ( $ns !== null ) {
					$finalNsId = $ns;
				} else {
					$ns = $this->language->getNsIndex( $value );
					if ( $ns !== false ) {
						$finalNsId = $ns;
					}
				}
			}
		}

		if ( $finalNsId === null ) {
			return StatusValue::newFatal( 'inputprocessor-error-namespace-invalid', $fieldKey, $value );
		}

		if ( $this->mustBeContent && !$this->namespaceInfo->isContent( $finalNsId ) ) {
			return StatusValue::newFatal( 'inputprocessor-error-namespace-not-content', $fieldKey, $value );
		}

		if ( $this->mustBeSubject && !$this->namespaceInfo->isSubject( $finalNsId ) ) {
			return StatusValue::newFatal( 'inputprocessor-error-namespace-not-subject', $fieldKey, $value );
		}

		if ( $this->mustBeTalk && !$this->namespaceInfo->isTalk( $finalNsId ) ) {
			return StatusValue::newFatal( 'inputprocessor-error-namespace-not-talk', $fieldKey, $value );
		}

		return StatusValue::newGood( $finalNsId );
	}

	/**
	 * @return mixed
	 */
	public function jsonSerialize(): mixed {
		return array_merge( parent::jsonSerialize(), [
			'type' => 'namespace',
			'mustBeContent' => $this->mustBeContent,
			'mustBeSubject' => $this->mustBeSubject,
			'mustBeTalk' => $this->mustBeTalk,
		] );
	}
}
