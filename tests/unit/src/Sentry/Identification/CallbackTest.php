<?php
/**
 * WatchTower Authentication system
 *
 * @license   MIT License
 * @author    David Lundgren
*/
namespace WatchTower\Test\Sentry\Identification;

use PHPUnit\Framework\TestCase;
use WatchTower\Event\Authenticate;
use WatchTower\Event\Event;
use WatchTower\Identity\GenericIdentity;
use WatchTower\Sentry\Identification\Callback;

/**
 * Unit test for the identification callback sentry
 *
 * @package WatchTower\Test\Sentry\Identification
 */
class CallbackTest
	extends TestCase
{
	public function testOnlyRunsOnIdentifyEvent()
	{
		$id = new GenericIdentity('superman');
		$id->setCredential('tower');
		$e = new Authenticate($id);
		$i = new Callback('ident1', function(Event $e) {
			throw new \Exception("should not run");
		});

		self::assertNull($i->discern($e));
	}
}
