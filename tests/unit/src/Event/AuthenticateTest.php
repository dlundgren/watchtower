<?php
/**
 * WatchTower Authentication system
 *
 * @license   MIT License
 * @author    David Lundgren
*/
namespace WatchTower\Test\Event;

use PHPUnit\Framework\TestCase;
use WatchTower\Event\Authenticate;
use WatchTower\Identity\GenericIdentity;

/**
 * Unit tests for the Authenticate event
 *
 * @package WatchTower\Test\Event
 */
class AuthenticateTest
	extends TestCase
{
	public function testConstructorThrowsExceptionWithoutIdentity()
	{
		$this->expectException('InvalidArgumentException');
		$this->expectExceptionMessage('Incomplete identity, missing identity');
		new Authenticate(new GenericIdentity(null));
	}

	public function testConstructorThrowsExceptionWithoutCredential()
	{
		$this->expectException('InvalidArgumentException');
		$this->expectExceptionMessage('Incomplete identity, missing credential');
		new Authenticate(new GenericIdentity('hi'));
	}
}
