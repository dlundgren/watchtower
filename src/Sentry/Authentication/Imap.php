<?php
/**
 * WatchTower Authentication system
 *
 * @license   MIT License
 * @author    David Lundgren
 */

namespace WatchTower\Sentry\Authentication;

use WatchTower\Exception\InvalidArgument;
use WatchTower\Event\Authenticate;
use WatchTower\Event\Event;
use WatchTower\Sentry\Sentry;
use WatchTower\Sentry\Traits\ReturnsName;
use WatchTower\Sentry\Traits\SetsErrorOnEvent;

/**
 * IMAP authentication sentry using PHP imap extension
 *
 * DSN format: imaps?://hostname:hostport
 *
 * @package WatchTower\Authentication\Adapter
 */
class Imap
	implements Sentry
{
	use ReturnsName, SetsErrorOnEvent;

	/**
	 * @var string The name of the sentry
	 */
	private $name = 'imap';

	/**
	 * @var string The server to connect to
	 */
	private $server;

	/**
	 * @var int The port to connect to the server on
	 */
	private $port;

	/**
	 * @var null|string Extra data to append to the username
	 */
	private $appendToUsername;

	/**
	 * @var bool Whether or not to stop further execution or fallthrough
	 */
	private $breakChainOnFailure;

	/**
	 * Initializes the IMAP settings
	 *
	 * @param string $name                The name of the sentry
	 * @param string $dsn                 The connection string for the server
	 * @param string $appendToUsername    Additional data to append to the username
	 * @param bool   $breakChainOnFailure Stop on failure
	 */
	public function __construct($name, $dsn, $appendToUsername = null, $breakChainOnFailure = false)
	{
		$this->name                = $name;
		$this->appendToUsername    = $appendToUsername;
		$this->breakChainOnFailure = $breakChainOnFailure;

		$parsed = parse_url($dsn);
		if (!isset($parsed['host'])) {
			throw new InvalidArgument("DSN is missing the server name");
		}

		$this->server = (isset($parsed['scheme']) ? $parsed['scheme'] : 'imap') . "://{$parsed['host']}";
		$this->port   = isset($parsed['port']) ? $parsed['port'] : 143;
	}

	/**
	 * Returns whether or not the given identity/credential are valid
	 *
	 * @param Event $event
	 *
	 * @return mixed|void
	 */
	public function discern(Event $event)
	{
		if (!($event instanceof Authenticate)) {
			return;
		}

		$identity = $event->identity();
		$imap     = imap_open($this->server, $identity->identity() . $this->appendToUsername, $identity->credential());

		if ($imap === false) {
			if (strpos(imap_last_error(), 'Connection timed out') !== false) {
				$this->setErrorOnEvent($event, Sentry::INTERNAL, "Connection timed out");
			}
			else {
				$this->setErrorOnEvent($event, Sentry::INVALID, "Invalid Credentials");
			}
		}

		imap_close($imap);
	}
}
