<?php
/**
 * WatchTower Authentication system
 *
 * @license   MIT License
 * @author    David Lundgren
 */

namespace WatchTower\Event;

use WatchTower\Identity\Identity;

/**
 * Event interface for handling events WatchTower
 *
 * This operates some what like Javascript event objects in that further processing can be stopped by calling the
 * stopPropagation() method. This is useful for those times when an adapter must present failure even if there are
 * others in the list to listen.
 *
 * @package WatchTower
 */
interface Event
{
	/**
	 * Returns the name of the event
	 *
	 * @return string
	 */
	public function name();

	/**
	 * Returns the Identity
	 *
	 * @return Identity
	 */
	public function identity();

	/**
	 * Returns whether or not the propagation is stopped
	 *
	 * @return bool
	 */
	public function isPropagationStopped();

	/**
	 * Stops the propagation
	 */
	public function stopPropagation();

	/**
	 * Trigger an error
	 *
	 * @param int    $code One of the \WatchTower\Sentry\Sentry constants
	 * @param string $message
	 *
	 * @return mixed
	 */
	public function triggerError($code, $message);

	/**
	 * Returns whether or not there are errors
	 *
	 * @return bool
	 */
	public function hasError();

	/**
	 * Returns the list of errors
	 *
	 * @return array
	 */
	public function error();
}
