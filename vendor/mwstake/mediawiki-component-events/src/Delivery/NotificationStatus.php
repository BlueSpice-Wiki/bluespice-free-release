<?php

declare( strict_types=1 );

namespace MWStake\MediaWiki\Component\Events\Delivery;

use DateTime;
use InvalidArgumentException;
use JsonSerializable;

class NotificationStatus implements JsonSerializable {
	public const STATUS_PENDING = "pending";
	public const STATUS_COMPLETED = "completed";
	public const STATUS_FAILED = "failed";

	/** @var DateTime|null */
	private $time;
	/** @var string|null */
	private $error;
	/** @var string|null */
	private $status;

	/**
	 * @param string|null $status
	 * @param string|null $error
	 * @param DateTime|null $time
	 */
	public function __construct( ?string $status = null, ?string $error = '', ?DateTime $time = null ) {
		if ( $status === null ) {
			$status = static::STATUS_PENDING;
		}
		$this->setStatus( $status );

		$this->error = $error;
		$this->time = $time;
	}

	/**
	 * @return bool
	 */
	public function isCompleted(): bool {
		return $this->status === static::STATUS_COMPLETED;
	}

	/**
	 * @return bool
	 */
	public function isFailed(): bool {
		return $this->status === static::STATUS_FAILED;
	}

	/**
	 * @return bool
	 */
	public function isPending(): bool {
		return !$this->status || $this->status === static::STATUS_PENDING;
	}

	/**
	 * @return string
	 */
	public function getStatus(): string {
		return $this->status;
	}

	/**
	 * @param string $status
	 *
	 * @return void
	 */
	public function setStatus( string $status ) {
		// Reject if status is not supported
		if ( !in_array( $status, [ static::STATUS_PENDING, static::STATUS_COMPLETED, static::STATUS_FAILED ] ) ) {
			throw new InvalidArgumentException( "Invalid status: $status" );
		}
		$this->status = $status;
	}

	/**
	 * @param string|null $errorMessage
	 *
	 * @return void
	 */
	public function markAsFailed( ?string $errorMessage ) {
		$this->status = static::STATUS_FAILED;
		$this->error = $errorMessage;
		$this->time = new DateTime();
	}

	/**
	 * @return void
	 */
	public function markAsCompleted() {
		$this->status = static::STATUS_COMPLETED;
		$this->time = new DateTime();
	}

	/**
	 * @return string
	 */
	public function getErrorMessage(): string {
		return $this->error;
	}

	/**
	 * @return DateTime|null
	 */
	public function getTime(): ?DateTime {
		return $this->time;
	}

	/**
	 * @return array
	 */
	public function jsonSerialize(): array {
		return [
			'status' => $this->status,
			'error' => $this->error,
			'time' => $this->time ? $this->time->format( 'YmdHis' ) : null,
		];
	}
}
