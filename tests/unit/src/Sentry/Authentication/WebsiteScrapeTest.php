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

use org\bovigo\vfs\vfsStream;
use WatchTower\Event\Authenticate;
use WatchTower\Identity\GenericIdentity;
use WatchTower\Sentry\Authentication\WebsiteScrape;

/**
 * Unit test for the WebsiteScrape Authentication adapter
 *
 * @package             WatchTower\Authentication\Adapter
 */
class WebsiteScrapeTest
	extends \PHPUnit_Framework_TestCase
{
	private $vfs;

	public function setUp()
	{
		// since we use file_get_contents() we can actually use a vfs
		// to get our responses
		$this->vfs = vfsStream::setup(
			'auth', null,
			[
				'test'    => [
					'test' => 'true'
				],
				'invalid' => [
					'password' => 'false',
				],
				'uhoh'    => []
			]);
	}

	private function buildAuthenticateEvent()
	{
		$id = new GenericIdentity('test');
		$id->setCredential('test');

		return new Authenticate($id);
	}

	public function testNameReturns()
	{
		$w = new WebsiteScrape('ws-test', 'http://super', 'woot');
		self::assertEquals('ws-test', $w->name());
	}

	public function testDiscernIgnoresNonAuthenticateEvents()
	{
		$ws        = new WebsiteScrape('ws-test', 'http://servername', 'ack');
		$e = $this->getMock('WatchTower\Event\AbstractEvent', ['discern']);
		$e->expects($this->never())->method('discern')->willThrowException(new \Exception('Should not call'));
		$ws->discern($e);
	}

	public function testDiscernOk()
	{
		$url   = $this->vfs->url('auth');
		$ws    = new WebsiteScrape('ws-test', "{$url}/{{username}}/{{password}}", 'true');
		$event = $this->buildAuthenticateEvent();
		$ws->discern($event);
		self::assertFalse($event->hasError());
	}

	public function testAuthenticateInvalidCredentials()
	{
		$url   = $this->vfs->url('auth');
		$ws    = new WebsiteScrape('ws-test', "{$url}/invalid/password", 'true');
		$event = $this->buildAuthenticateEvent();
		$ws->discern($event);
		self::assertTrue($event->hasError());
		self::assertContains("[ws-test] Invalid credentials", $event->error());
	}

	public function testAuthenticateInvalidUrl()
	{
		$url   = $this->vfs->url('auth');
		$ws    = new WebsiteScrape('ws-test', "{$url}/ack", 'true');
		$event = $this->buildAuthenticateEvent();
		$ws->discern($event);
		self::assertTrue($event->hasError());
		self::assertContains("[ws-test] Unable to contact the url: vfs://auth/ack", $event->error());
	}
}
