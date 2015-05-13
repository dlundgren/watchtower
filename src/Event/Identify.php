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

/**
 * Identify event
 *
 * Handles the process of identifying an identity
 *
 * @package WatchTower\Event
 */
class Identify
	extends AbstractEvent
{
	/**
	 * Validates the Identity
	 *
	 * @param Identity $identity
	 */
	public function __construct(Identity $identity)
	{
		$this->identity = $identity;
	}
}
