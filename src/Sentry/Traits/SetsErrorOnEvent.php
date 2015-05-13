<?php
/**
 * WatchTower Authentication system
 *
 * @license   MIT License
 * @author    David Lundgren
 * @link      http://dlundgren.github.io/watchtower
 * @copyright 2015. David Lundgren
 */
namespace WatchTower\Sentry\Traits;

use WatchTower\Event\Event;

/**
 * Common trait for setting errors on the credentials object
 *
 * @package WatchTower\Traits
 */
trait SetsErrorOnEvent
{
	/**
	 * Handles setting the error on the credentials
	 *
	 * @param Event  $event
	 * @param int    $code
	 * @param string $message
	 * @return void
	 */
	private function setErrorOnEvent(Event $event, $code, $message)
	{
		if ($this->breakChainOnFailure) {
			$event->stopPropagation();
		}
		$event->triggerError($code, "[{$this->name}] {$message}");

		return;
	}
}
