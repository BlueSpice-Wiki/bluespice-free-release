<?php

namespace MWStake\MediaWiki\Component\GenericTagHandler;

use JsonSerializable;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\FormEngine\FormLoaderSpecification;
use MWStake\MediaWiki\Component\FormEngine\IFormSpecification;

class ClientTagSpecification implements JsonSerializable {

	/**
	 * @param string $classname
	 * @param Message $description
	 * @param IFormSpecification|FormLoaderSpecification|null $formSpecification
	 * @param Message $menuMessage
	 * @param string|null $icon
	 * @param bool $hideMainInput
	 */
	public function __construct(
		private readonly string $classname,
		private readonly Message $description,
		private readonly IFormSpecification|FormLoaderSpecification|null $formSpecification,
		private readonly Message $menuMessage,
		private readonly ?string $icon = null,
		private readonly bool $hideMainInput = true
	) {
	}

	/**
	 * @return mixed
	 */
	public function jsonSerialize(): mixed {
		$formSpec = null;
		if ( $this->formSpecification instanceof IFormSpecification ) {
			$formSpec = $this->formSpecification->getSerialized();
		} elseif ( $this->formSpecification instanceof FormLoaderSpecification ) {
			$formSpec = [
				'type' => 'async',
				'modules' => $this->formSpecification->getResourceLoaderModules(),
				'formClass' => $this->formSpecification->getFormClass()
			];
		}
		return [
			'classname' => $this->classname,
			'description' => $this->description->text(),
			'formSpecification' => $formSpec,
			'menuMessage' => $this->menuMessage->text(),
			'icon' => $this->icon,
			'hideMainInput' => $this->hideMainInput
		];
	}
}
