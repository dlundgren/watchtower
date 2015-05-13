<?php
/**
 * WatchTower Authentication system
 *
 * @license   MIT License
 * @author    David Lundgren
 * @link      http://dlundgren.github.io/watchtower
 * @copyright 2015. David Lundgren
 */
namespace WatchTower\Sentry\Identification\Stealth;

use WatchTower\Event\Event;
use WatchTower\Event\Identify;
use WatchTower\Sentry\Sentry;
use WatchTower\Sentry\Traits\ReturnsName;

/**
 * Identity Identification from the php session
 *
 * This sentry assumes it is fully authoritative, and will stop event propagation
 */
class PhpSession
	implements Sentry
{
	use ReturnsName;

	/**
	 * @var string the Name of the adapter
	 */
	protected $name;

	/**
	 * @var string the name of the namespace to look for in the $_SESSION global
	 */
	protected $namespace;

	/**
	 * Initialize
	 *
	 * @param string $name      The name of this sentry
	 * @param string $namespace The session namespace the identity is stored under
	 */
	public function __construct($name, $namespace)
	{
		$this->name      = $name;
		$this->namespace = $namespace;
	}

	/**
	 * Identify
	 *
	 * We are authoritative and so we stop event propagation as we have fully identified the user
	 *
	 * @param Event $event
	 * @return int
	 */
	public function discern(Event $event)
	{
		if (session_status() === PHP_SESSION_ACTIVE && $event instanceof Identify && isset($_SESSION[$this->namespace]['identity'])) {
			$event->identity()->setIdentified($_SESSION[$this->namespace]['identity']);
			$event->stopPropagation();
		}
	}
}
