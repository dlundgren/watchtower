<?php
/**
 * WatchTower Authentication system
 *
 * @license   MIT License
 * @author    David Lundgren
 */

namespace WatchTower\Sentry\Identification\Stealth;

use WatchTower\Event\Event;
use WatchTower\Event\Identify;
use WatchTower\Sentry\Sentry;
use WatchTower\Sentry\Traits\ReturnsName;

/**
 * Transparent CIDR block identification
 *
 * @package WatchTower\Authentication\Adapter
 */
class IpBasic
	implements Sentry
{
	use ReturnsName;

	/**
	 * @var string The IP to check
	 */
	private $ip;

	/**
	 * @var string The name of this adapter
	 */
	private $name = 'ipbasic';

	/**
	 * @var array The CIDR blocks that are allowed
	 */
	private $allowedCidrBlocks = [];

	/**
	 * Sets up the class
	 *
	 * @param string       $name              The name of the adapter
	 * @param string|array $allowedCidrBlocks The IP blocks to allow
	 */
	public function __construct($name, $allowedCidrBlocks)
	{
		$this->name              = $name;
		$this->allowedCidrBlocks = (array)$allowedCidrBlocks;

		if (isset($_SERVER['REMOTE_ADDR'])) {
			$this->ip = $_SERVER['REMOTE_ADDR'];
		}
	}

	/**
	 * Authenticates the credentials
	 *
	 * The ip must be set in order for this to bother test
	 *
	 * @param Event $event
	 *
	 * @return int
	 */
	public function discern(Event $event)
	{
		if ($event instanceof Identify && isset($this->ip)) {
			$ip       = ip2long($this->ip);
			$identity = $event->identity();
			foreach ($this->allowedCidrBlocks as $cidr) {
				list($quad, $bits) = explode('/', $cidr);
				$bits = 32 - intval($bits);
				if (($ip >> $bits) == (ip2long($quad) >> $bits)) {
					$obj     = new \stdClass();
					$obj->ip = $ip;
					$identity->setIdentified($obj);
				}
			}
		}
	}
}
