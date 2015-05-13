<?php
/**
 * WatchTower Authentication system
 *
 * @license   MIT License
 * @author    David Lundgren
 * @link      http://dlundgren.github.io/watchtower
 * @copyright 2015. David Lundgren
 */
namespace WatchTower\Sentry\Identification;

use WatchTower\Event\Event;
use WatchTower\Event\Identify;
use WatchTower\Sentry\Sentry;
use WatchTower\Sentry\Traits\ReturnsName;
use WatchTower\Sentry\Traits\SetsErrorOnEvent;

/**
 * InMemory identifier adapter
 *
 * Primarily used for testing
 *
 * @package WatchTower\Identification\Adapter
 */
class InMemory
	implements Sentry
{
	use ReturnsName, SetsErrorOnEvent;

	/**
	 * @var string Name of this adapter
	 */
	private $breakChainOnFailure = false;

	/**
	 * @var string Name of this adapter
	 */
	private $name = 'in-memory';

	/**
	 * @var array List of ['identity:credentials' => obj]
	 */
	private $data = [];

	/**
	 * Initializes the InMemory settings
	 *
	 * @param string $name The name of this sentry
	 * @param bool   $breakChainOnFailure   If there is a problem should it cause a stop?
	 */
	public function __construct($name, $breakChainOnFailure = false)
	{
		$this->name                = $name;
		$this->breakChainOnFailure = $breakChainOnFailure;
	}

	/**
	 * Adds an identity/credential pair
	 *
	 * @param string $identity
	 * @param mixed $value
	 */
	public function add($identity, $value)
	{
		$this->data[$identity] = $value;
	}

	/**
	 * Attempts to identify the user based on the passed in credentials
	 *
	 * @param Event $event
	 * @return bool
	 */
	public function discern(Event $event)
	{
		if ($event instanceof Identify) {
			$identity = $event->identity();
			if (isset($this->data[$identity->identity()])) {
				$identity->setIdentified($this->data[$identity->identity()]);
			}
			else {
				$this->setErrorOnEvent($event, Sentry::NOT_FOUND, "Identity Not Found");
			}
		}
	}
}
