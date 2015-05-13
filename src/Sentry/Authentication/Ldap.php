<?php
/**
 * WatchTower Authentication system
 *
 * @license   MIT License
 * @author    David Lundgren
 * @link      http://dlundgren.github.io/watchtower
 * @copyright 2015. David Lundgren
 */
namespace WatchTower\Sentry\Authentication;

use WatchTower\Exception\InvalidArgument;
use WatchTower\Event\Authenticate;
use WatchTower\Event\Event;
use WatchTower\Sentry\Sentry;
use WatchTower\Sentry\Traits\ReturnsName;

/**
 * LDAP authentication sentry
 *
 * DSN format: ldaps?://binddn:bindpassword@hostname:hostport/basedn
 *
 * @package WatchTower\Authentication\Adapter
 */
class Ldap
	implements Sentry
{
	use ReturnsName;

	/**
	 * @var string Name of this sentry
	 */
	private $name = 'ldap';

	/**
	 * @var string Server to connect to
	 */
	private $server;

	/**
	 * @var int Server port to connect to
	 */
	private $port;

	/**
	 * @var string The base DN to use for queries
	 */
	private $baseDn;

	/**
	 * @var string The user to bind as to search
	 */
	private $bindDn;

	/**
	 * @var string The password for the bind dn
	 */
	private $bindPassword;

	/**
	 * @var string The identity field (AD would use sAMAccountName)
	 */
	private $identityField;

	/**
	 * @var array Groups if any the user needs to be a part of
	 */
	private $groups = [];

	/**
	 * @var bool Breaks the authentication when it fails
	 */
	private $breakChainOnFailure = false;

	/**
	 * Initializes the LDAP settings
	 *
	 * @param string            $name                The name of this sentry
	 * @param string            $dsn                 The DSN of the server
	 * @param string            $identityField       The field to search on
	 * @param string|array|null $groups              List of groups that users should be a part of
	 * @param bool              $breakChainOnFailure Whether or not to break the authentication on failure
	 */
	public function __construct($name, $dsn, $identityField, $groups = null, $breakChainOnFailure = false)
	{
		$this->name                = $name;
		$this->identityField       = $identityField;
		$this->groups              = isset($groups) ? (array)$groups : [];
		$this->breakChainOnFailure = $breakChainOnFailure;

		$parsed = parse_url($dsn);
		if (!isset($parsed['host'])) {
			throw new InvalidArgument("DSN is missing the server name");
		}
		if (!isset($parsed['path'])) {
			throw new InvalidArgument("DSN is missing the base dn");
		}

		$this->server       = (isset($parsed['scheme']) ? $parsed['scheme'] : 'ldap') . "://{$parsed['host']}";
		$this->port         = isset($parsed['port']) ? $parsed['port'] : 389;
		$this->bindDn       = isset($parsed['user']) ? $parsed['user'] : '';
		$this->bindPassword = isset($parsed['pass']) ? $parsed['pass'] : '';
		$this->baseDn       = isset($parsed['path']) ? trim($parsed['path'], '/') : '';
	}

	/**
	 * Returns whether or not the given identity/credential are valid
	 *
	 * @param Event $event
	 * @return mixed|void
	 */
	public function discern(Event $event)
	{
		if (!($event instanceof Authenticate)) {
			return;
		}

		$identity = $event->identity();
		$ldap     = ldap_connect($this->server, $this->port);
		if (!$ldap) {
			return $this->setErrorOnEvent($ldap, $event, Sentry::INTERNAL, "Unable to connect to {$this->server}");
		}

		if (!empty($this->bindDn)) {
			$bind = ldap_bind($ldap, $this->bindDn, $this->bindPassword);
			if (!$bind) {
				return $this->setErrorOnEvent($ldap, $event, Sentry::INTERNAL, "Could not bind to {$this->server} as {$this->bindDn}");
			}
		}

		$userDn = $this->getIdentityDn($ldap, $event);
		if ($userDn === false) {
			return false;
		}

		if (ldap_bind($ldap, $userDn, $identity->credential()) === false) {
			return $this->setErrorOnEvent($ldap, $event, Sentry::INVALID, "Invalid credentials");
		}
		elseif (!empty($this->groups)) {
			$this->checkGroups($ldap, $event);
		}

		is_resource($ldap) && ldap_unbind($ldap);
	}

	/**
	 * Returns the Identity DN
	 *
	 * @param             $ldap
	 * @param Event       $event
	 * @return bool|int|string
	 */
	private function getIdentityDn($ldap, Event $event)
	{
		$value        = false;
		$searchResult = ldap_search(
			$ldap, $this->baseDn, sprintf(
			"%s=%s", $this->identityField, $event->identity()->identity()));
		if ($searchResult === false) {
			// failed to search (unknown reason)
			$this->setErrorOnEvent($ldap, $event, Sentry::INTERNAL, "Unable to search on $this->server");
		}
		else {
			$entry = ldap_first_entry($ldap, $searchResult);
			ldap_free_result($searchResult);
			if ($entry === false) {
				$this->setErrorOnEvent($ldap, $event, Sentry::NOT_FOUND, "User not found");
			}
			else {
				$value = ldap_get_dn($ldap, $entry);
			}
		}

		return $value;
	}

	/**
	 * Checks that the LDAP entry has one of the listed groups
	 *
	 * @param       $ldap
	 * @param Event $event
	 * @return mixed
	 */
	private function checkGroups($ldap, Event $event)
	{
		$searchResult = ldap_search(
			$ldap, $this->baseDn, sprintf(
			"%s=%s", $this->identityField, $event->identity()->identity()), ['memberOf']);
		if ($searchResult === false) {
			// failed to search (unknown reason)
			$code   = Sentry::INTERNAL;
			$reason = "Unable to search for groups on $this->server";
		}
		else {
			$code   = Sentry::INVALID;
			$reason = "Identity has no groups assigned";
			$attrs  = ldap_get_attributes($ldap, ldap_first_entry($ldap, $searchResult));
			ldap_free_result($searchResult);
			if (isset($attrs['memberOf']['count']) && $attrs['memberOf']['count'] > 0) {
				foreach ($this->groups as $group) {
					if (in_array($group, $attrs['memberOf'])) {
						// return early if a member of any group
						return true;
					}
				}

				// if we haven't returned by now there is a problem
				$reason = "Not in allowed groups";
			}
		}
		$this->setErrorOnEvent($ldap, $event, $code, $reason);
	}

	/**
	 * Handles setting the error on the credentials
	 *
	 * Returns STATUS_ERROR unless BreakChainOnFailure is set
	 *
	 * @param        $ldap
	 * @param Event  $event
	 * @param int    $code
	 * @param string $message
	 * @return int
	 */
	private function setErrorOnEvent($ldap, $event, $code, $message)
	{
		if ($this->breakChainOnFailure) {
			$event->stopPropagation();
		}
		$event->triggerError($code, "[{$this->name}] {$message}");
		if (is_resource($ldap)) {
			ldap_unbind($ldap);
		}
	}
}
