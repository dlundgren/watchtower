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

use WatchTower\Sentry\Authentication\Imap;
use WatchTower\Identity\GenericIdentity;
use WatchTower\Event\Authenticate;

/**
 * Unit tests for the Imap Authentication Adapter
 *
 * @package             WatchTower\Authentication\Adapter
 */
class ImapTest
	extends \PHPUnit_Framework_TestCase
{
	private $mockImapResource;

	public function setUp()
	{
		require_once SUPPORT_FILE_PATH . '/imap-functions.php';

		$this->mockImapResource = \WatchTower\Sentry\Authentication\MockImap::$mock = new \stdClass();

		foreach (array_values((array)$this->mockImapResource) as $k) {
			if (isset($this->mockImapResource->$k)) {
				unset($this->mockImapResource->$k);
			}
		}
	}

	private function buildAuthenticateEvent()
	{
		$id = new GenericIdentity('test');
		$id->setCredential('test');

		return new Authenticate($id);
	}

	public function testNameReturns()
	{
		$i = new Imap('imap-test', 'imap://superman');
		self::assertEquals('imap-test', $i->name());
	}

	public function testConstructorThrowsInvalidArgumentsOnBadDsn()
	{
		$this->setExpectedException('InvalidArgumentException', 'DSN is missing the server name');
		new Imap('imap-test', 'imap://', null, null, false);
	}

	public function testDiscernIgnoresNonAuthenticateEvents()
	{
		$imap = new Imap('imap-test', 'imap://servername');
		$e    = $this->getMock('WatchTower\Event\AbstractEvent', ['discern']);
		$e->expects($this->never())->method('discern')->willThrowException(new \Exception('Should not call'));
		$imap->discern($e);
	}

	public function testDiscernOk()
	{
		$imap  = new Imap('imap-test', 'imap://servername');
		$event = $this->buildAuthenticateEvent();
		$imap->discern($event);
		// this indicates that it was successfully processed
		self::assertFalse($event->hasError());
	}

	public function testDiscernFailConnection()
	{
		$this->mockImapResource->failOpenConnection = true;
		$imap                                       = new Imap('imap-test', 'imap://servername');
		$event                                      = $this->buildAuthenticateEvent();
		$imap->discern($event);
		self::assertTrue($event->hasError());
		self::assertContains("[imap-test] Connection timed out", $event->error());
	}

	public function testDiscernFailInvalid()
	{
		$this->mockImapResource->failOpen = true;
		$imap                             = new Imap('imap-test', 'imap://servername');
		$event                            = $this->buildAuthenticateEvent();
		$imap->discern($event);
		self::assertTrue($event->hasError());
		self::assertContains("[imap-test] Invalid Credentials", $event->error());
	}
}
