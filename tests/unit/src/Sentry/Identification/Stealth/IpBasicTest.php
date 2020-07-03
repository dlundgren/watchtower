<?php
/**
 * WatchTower Authentication system
 *
 * @license   MIT License
 * @author    David Lundgren
*/
namespace WatchTower\Test\Sentry\Identification;

use PHPUnit\Framework\TestCase;
use WatchTower\Event\AbstractEvent;
use WatchTower\Event\Identify;
use WatchTower\Identity\GenericIdentity;
use WatchTower\Sentry\Identification\Stealth\IpBasic;

/**
 * Unit test for the IpBasic Identification sentry
 *
 * This tests that each CIDR block is calculated properly, and that the TRANSPARENT_IDENTITY is set on the system
 */
class IpBasicTest
	extends TestCase
{
	public function provideValidCidrFor1_2_3_4()
	{
		return [
			['1.0.0.0/1'],
			['1.0.0.0/2'],
			['1.0.0.0/3'],
			['1.0.0.0/4'],
			['1.0.0.0/5'],
			['1.0.0.0/6'],
			['1.0.0.0/7'],
			['1.0.0.0/8'],
			['1.0.0.0/9'],
			['1.0.0.0/10'],
			['1.0.0.0/11'],
			['1.0.0.0/12'],
			['1.0.0.0/13'],
			['1.0.0.0/14'],
			['1.2.0.0/15'],
			['1.2.0.0/16'],
			['1.2.0.0/17'],
			['1.2.0.0/18'],
			['1.2.0.0/19'],
			['1.2.0.0/20'],
			['1.2.0.0/21'],
			['1.2.0.0/22'],
			['1.2.3.0/23'],
			['1.2.3.0/24'],
			['1.2.3.0/25'],
			['1.2.3.0/26'],
			['1.2.3.0/27'],
			['1.2.3.0/28'],
			['1.2.3.0/29'],
			['1.2.3.4/30'],
			['1.2.3.4/31'],
			['1.2.3.4/32']
		];
	}

	public function provideInvalidCidrFor1_2_3_4()
	{
		return [
			['129.0.0.0/1'],
			['65.0.0.0/2'],
			['33.0.0.0/3'],
			['17.0.0.0/4'],
			['9.0.0.0/5'],
			['5.0.0.0/6'],
			['3.0.0.0/7'],
			['2.0.0.0/8'],
			['2.0.0.0/9'],
			['2.0.0.0/10'],
			['2.0.0.0/11'],
			['2.0.0.0/12'],
			['2.0.0.0/13'],
			['2.0.0.0/14'],
			['2.0.0.0/15'],
			['1.3.0.0/16'],
			['1.3.0.0/17'],
			['1.3.0.0/18'],
			['1.3.0.0/19'],
			['1.3.0.0/20'],
			['1.3.0.0/21'],
			['1.3.0.0/22'],
			['1.2.0.0/23'],
			['1.2.1.0/24'],
			['1.2.1.0/25'],
			['1.2.1.0/26'],
			['1.2.1.0/27'],
			['1.2.1.0/28'],
			['1.2.1.0/29'],
			['1.2.3.3/30'],
			['1.2.3.3/31'],
			['1.2.3.5/32']
		];
	}

	private function buildIdentifyEvent()
	{
		return new Identify(new GenericIdentity(null));
	}

	public function testNameReturns()
	{
		$i = new IpBasic('ib-test', '0.0.0.0/0');
		self::assertEquals('ib-test', $i->name());
	}

	public function testDiscernIgnoresNonIdentifyEvents()
	{
		$imap = new IpBasic('ip-test', []);
		$e    = $this->createMock(AbstractEvent::class);
		$e->expects($this->never())->method('identity')->willThrowException(new \Exception('Should not call'));
		$imap->discern($e);
	}

	/**
	 * @dataProvider provideValidCidrFor1_2_3_4
	 */
	public function testDiscernOk($cidr)
	{
		$_SERVER['REMOTE_ADDR'] = '1.2.3.4';
		$e = $this->buildIdentifyEvent();
		(new IpBasic('ipbasic-test', [$cidr]))->discern($e);
		self::assertFalse($e->hasError());
		self::assertTrue($e->identity()->isIdentified());
	}

	/**
	 * @dataProvider provideInvalidCidrFor1_2_3_4
	 */
	public function testAuthenticateInvalidCredentials($cidr)
	{
		$_SERVER['REMOTE_ADDR'] = '1.2.3.4';
		$e = $this->buildIdentifyEvent();
		(new IpBasic('ipbasic-test', [$cidr]))->discern($e);

		// N.B - Not testing hasError() as this is a silent identification
		self::assertFalse($e->identity()->isIdentified());
	}
}
