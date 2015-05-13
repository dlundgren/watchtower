<?php
/**
 * WatchTower Authentication system
 *
 * @license   MIT License
 * @author    David Lundgren
 * @link      http://dlundgren.github.io/watchtower
 * @copyright 2015. David Lundgren
 */
namespace WatchTower\Event;

use WatchTower\Identity\Identity;
use WatchTower\Sentry\Sentry;

/**
 * Abstract Event class
 *
 * @package WatchTower\Event
 */
abstract class AbstractEvent
	implements Event
{
	/**
	 * @var bool Propagation is stopped
	 */
	private $stopPropagation = false;

	/**
	 * @var array The error message
	 */
	private $error;

	/**
	 * @var int Error code
	 */
	private $errorCode = Sentry::OK;

	/**
	 * @var Identity
	 */
	protected $identity;

	/**
	 * Returns the name of the event
	 *
	 * @return string
	 */
	public function name()
	{
		return get_class($this);
	}

	/**
	 * Returns if the propagation is stopped
	 *
	 * @return bool
	 */
	public function isPropagationStopped()
	{
		return isset($this->stopPropagation) ? $this->stopPropagation : false;
	}

	/**
	 * Stops the propagation
	 */
	public function stopPropagation()
	{
		$this->stopPropagation = true;
	}

	/**
	 * Trigger an error
	 *
	 * @param int    $code One of the \WatchTower\Sentry\Sentry constants
	 * @param string $message
	 * @return mixed
	 */
	public function triggerError($code, $message)
	{
		$this->errorCode = $code;
		$this->error     = $message;
	}

	/**
	 * Returns whether there are errors
	 *
	 * @return bool
	 */
	public function hasError()
	{
		return $this->errorCode !== Sentry::OK;
	}

	/**
	 * Returns the errors
	 *
	 * @return array
	 */
	public function error()
	{
		return $this->error;
	}

	/**
	 * Returns the Identity
	 *
	 * @return Identity
	 */
	public function identity()
	{
		return $this->identity;
	}
}
