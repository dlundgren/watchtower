<?php
/**
 * WatchTower Authentication system
 *
 * @license   MIT License
 * @author    David Lundgren
 * @link      http://dlundgren.github.io/watchtower
 * @copyright 2015. David Lundgren
 */
namespace WatchTower\Test\Sentry\Identification;

use WatchTower\Event\Identify;
use WatchTower\Identity\GenericIdentity;
use WatchTower\Sentry\Identification\InMemory;

/**
 * Unit tests for the InMemory Identification Adapter
 *
 */
class InMemoryTest
	extends \PHPUnit_Framework_TestCase
{
	public function testNameReturns()
	{
		$i = new InMemory('im-test');
		self::assertEquals('im-test', $i->name());
	}

	private function buildIdentifyEvent()
	{
		return new Identify(new GenericIdentity('test'));
	}

	public function testDiscernIgnoresNonIdentifyEvents()
	{
		$imap = new InMemory('im-test');
		$e    = $this->getMock('WatchTower\Event\AbstractEvent', ['discern']);
		$e->expects($this->never())->method('discern')->willThrowException(new \Exception('Should not call'));
		$imap->discern($e);
	}

	public function testDiscernOk()
	{
		$im = new InMemory('im-test');
		$e  = $this->buildIdentifyEvent();
		$im->add('test', (object)['name' => 'Test User']);
		$im->discern($e);

		self::assertFalse($e->hasError());
		self::assertTrue($e->identity()->isIdentified());
	}

	public function testDiscernFails()
	{
		$im = new InMemory('im-test');
		$e  = $this->buildIdentifyEvent();
		$im->discern($e);

		self::assertTrue($e->hasError());
		self::assertFalse($e->identity()->isIdentified());
	}

	public function testDiscernHonorsBreakOnFailure()
	{
		$im = new InMemory('im-test', true);
		$e  = $this->buildIdentifyEvent();
		$im->discern($e);

		self::assertTrue($e->isPropagationStopped());
	}
}
