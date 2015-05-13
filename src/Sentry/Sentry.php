<?php
/**
 * WatchTower Authentication system
 *
 * @license   MIT License
 * @author    David Lundgren
 * @link      http://dlundgren.github.io/watchtower
 * @copyright 2015. David Lundgren
 */
namespace WatchTower\Sentry;

use WatchTower\Event\Event;

interface Sentry
{
	const OK = 0;
	const INVALID = 1;
	const NOT_FOUND = 2;
	const AMBIGUOUS = 3;
	const INTERNAL = 4;

	/**
	 * Processes the event
	 *
	 * @param Event $event
	 * @return mixed
	 */
	public function discern(Event $event);
}
