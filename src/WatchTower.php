<?php
/**
 * WatchTower Authentication system
 *
 * @license   MIT License
 * @author    David Lundgren
 * @link      http://dlundgren.github.io/watchtower
 * @copyright 2015. David Lundgren
 */
namespace WatchTower;

use WatchTower\Identity\Identity;
use WatchTower\Identity\GenericIdentity;
use WatchTower\Event\Event;
use WatchTower\Event\Authenticate;
use WatchTower\Event\Identify;
use WatchTower\Sentry\Sentry;

/**
 * The WatchTower handles authentication
 *
 * It is loosely based off of the ZetaComponents Authentication component and is designed to be extended with
 * adapters to handle identification and authentication.
 *
 * The WatchTower uses a three level approach to authentication:
 *   - anonymous
 *   - identified
 *   - authenticated
 *
 * As such the system is designed to allow subscriptions to the two major life events of identify and authenticate.
 *
 * The Identity itself contains a group of functions designed to make the distinction between these states:
 *   - isAnonymous()
 *   - isIdentified()
 *   - isAuthenticated()
 *
 * @package WatchTower
 */
class WatchTower
{
	/**
	 * @var array List of sentries
	 */
	private $sentries = [];

	/**
	 * Subscribes to events that WatchTower may trigger
	 *
	 * The subscriber is responsible for filtering what it wants
	 *
	 * @param Sentry $sentry
	 */
	public function watch(Sentry $sentry)
	{
		$this->sentries[] = $sentry;
	}

	/**
	 * Attempts to identify the identity
	 *
	 * @param string $identity
	 * @return Identity
	 */
	public function identify($identity)
	{
		return $this->triggerAndReturnIdentity(new Identify(new GenericIdentity($identity)));
	}

	/**
	 * Authenticates using an identity object
	 *
	 * @param Identity $identity
	 * @return Identity
	 */
	public function authenticateWithIdentity(Identity $identity)
	{
		return $this->triggerAndReturnIdentity(new Authenticate($identity));
	}

	/**
	 * Authenticates the identity/credential
	 *
	 * @param string $identity
	 * @param string $credential
	 * @return Identity
	 */
	public function authenticate($identity, $credential)
	{
		$identity = $this->identify($identity);
		$identity->setCredential($credential);

		return $this->authenticateWithIdentity($identity);
	}

	/**
	 * Triggers the event and returns the identity
	 *
	 * @param Event $event
	 * @return Identity
	 */
	private function triggerAndReturnIdentity(Event $event)
	{
		if (empty($this->sentries)) {
			$event->triggerError(Sentry::INVALID, "No sentries available");
		}
		else {
			/** @var Sentry $sentry */
			foreach ($this->sentries as $sentry) {
				$sentry->discern($event);
				if ($event->isPropagationStopped()) {
					break;
				}
			}
		}

		/** @var Identity $identity */
		$identity = $event->identity();
		if ($event->hasError()) {
			$identity->addError($event->error());
		}
		elseif ($event instanceof Authenticate) {
			$identity->setAuthenticated();
		}

		return $identity;
	}
}
