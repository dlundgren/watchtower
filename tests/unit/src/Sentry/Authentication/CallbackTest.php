<?php
/**
 * WatchTower Authentication system
 *
 * @license   MIT License
 * @author    David Lundgren
 * @link      http://dlundgren.github.io/watchtower
 * @copyright 2015. David Lundgren
 */
namespace WatchTower\Test\Sentry\Authentication;

use PHPUnit\Framework\TestCase;
use WatchTower\Event\Identify;
use WatchTower\Event\Event;
use WatchTower\Identity\GenericIdentity;
use WatchTower\Sentry\Authentication\Callback;

/**
 * Unit test for the authentication callback sentry
 *
 * @package WatchTower\Test\Sentry\Authentication
 */
class CallbackTest
	extends TestCase
{
	public function testOnlyRunsOnIdentifyEvent()
	{
		$i = new Callback('ident1', function(Event $e) {
			throw new \Exception("should not run");
		});

		self::assertNull($i->discern(new Identify(new GenericIdentity('superman'))));
	}
}
