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

use WatchTower\Exception\InvalidArgument;
use WatchTower\Identity\Identity;

/**
 * Authenticate Event
 *
 * This handles ensuring that the Identity is set up properly for authentication
 *
 * @package WatchTower\Event
 */
class Authenticate
	extends AbstractEvent
{
	/**
	 * Validates the Identity
	 *
	 * @param Identity $identity
	 */
	public function __construct(Identity $identity)
	{
		if (empty($identity->identity())) {
			throw new InvalidArgument("Incomplete identity, missing identity");
		}

		if (empty($identity->credential())) {
			throw new InvalidArgument("Incomplete identity, missing credential");
		}

		$identity->lock();

		$this->identity = $identity;
	}

}
