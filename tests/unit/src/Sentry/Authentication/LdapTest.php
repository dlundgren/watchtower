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
use WatchTower\Event\AbstractEvent;
use WatchTower\Identity\GenericIdentity;
use WatchTower\Event\Authenticate;
use WatchTower\Sentry\Authentication\Ldap;

/**
 * Unit tests for the LDAP Authentication Adapter
 *
 * @package             WatchTower\Authentication\Adapter
 */
class LdapTest
	extends TestCase
{
	private $mockLdapResource;

	public function setUp(): void
	{
		require_once SUPPORT_FILE_PATH . '/ldap-functions.php';

		$this->mockLdapResource = \WatchTower\Sentry\Authentication\MockLdap::$mock = new \stdClass();

		foreach (array_values((array)$this->mockLdapResource) as $k) {
			if (isset($this->mockLdapResource->$k)) {
				unset($this->mockLdapResource->$k);
			}
		}
	}

	public function provideInvalidDsn()
	{
		return [
			['ldap://username:password/'],
			['ldap://servername']
		];
	}

	public function provideLdapFunctionFailuresAndMessages()
	{
		return [
			['failConnect', 'Unable to connect to ldap://servername'],
			['failBind', 'Could not bind to ldap://servername as username'],
			['failSearch', 'Unable to search on ldap://servername'],
			['failFirstEntry', 'User not found'],
			['failBindGetDn', 'Invalid credentials'],
		];
	}

	public function provideLdapGetAttributeInvalidForMemberOf()
	{
		return [
			[null],
			[['super' => 'woot']],
			[['memberOf' => 'haha']],
			[['memberOf' => ['count' => 0]]],
		];
	}

	private function buildAuthenticateEvent()
	{
		$id = new GenericIdentity('test');
		$id->setCredential('test');

		return new Authenticate($id);
	}

	public function testNameReturns()
	{
		$l = new Ldap('ldap-test', 'ldap://u:p@s/d=example', 'uid');
		self::assertEquals('ldap-test', $l->name());
	}

	/**
	 * @dataProvider provideInvalidDsn
	 * @param $payload
	 */
	public function testConstructorThrowsInvalidArgumentsOnBadDsn($payload)
	{
		$this->expectException('InvalidArgumentException');
		new Ldap('ldap-test', $payload, null, null, false);
	}

	public function testDiscernIgnoresNonAuthenticateEvents()
	{
		$l = new Ldap('ldap-test', 'ldap://u:p@s/d=example', 'uid');
		$e = $this->createMock(AbstractEvent::class);
		$e->expects($this->never())->method('identity')->willThrowException(new \Exception('Should not call'));
		$l->discern($e);
	}

	public function testDiscernOk()
	{
		$ldap  = new Ldap('ldap-test', 'ldap://username:password@servername/dc=example,dc=com', 'sAMAccountName');
		$event = $this->buildAuthenticateEvent();
		$ldap->discern($event);
		self::assertFalse($event->hasError());
	}

	public function testDiscernFailsWhenNotAbleToConnectToServer()
	{
		$ldap                                = new Ldap('ldap-test', 'ldap://username:password@servername/dc=example,dc=com', 'sAMAccountName', null, true);
		$event                               = $this->buildAuthenticateEvent();
		$this->mockLdapResource->failConnect = true;
		$ldap->discern($event);
		self::assertTrue($event->hasError());
		self::assertStringContainsString("[ldap-test] Unable to connect to ldap://servername", $event->error());
	}

	/**
	 * @dataProvider provideLdapFunctionFailuresAndMessages
	 */
	public function testDiscernOnLdapFailureScenarios($ldapFailure, $credentialError)
	{
		$ldap                                   = new Ldap('ldap-test', 'ldap://username:password@servername/dc=example,dc=com', 'sAMAccountName');
		$this->mockLdapResource->{$ldapFailure} = true;
		$event                                  = $this->buildAuthenticateEvent();
		$ldap->discern($event);
		self::assertTrue($event->hasError());
		self::assertStringContainsString($credentialError, $event->error());
	}

	public function testDiscernWithGroupAndFailureOnSearch()
	{
		$ldap                                    = new Ldap('ldap-test', 'ldap://username:password@servername/dc=example,dc=com', 'sAMAccountName', 'super');
		$this->mockLdapResource->failGroupSearch = true;
		$event                                   = $this->buildAuthenticateEvent();
		$ldap->discern($event);
		self::assertTrue($event->hasError());
		self::assertStringContainsString("[ldap-test] Unable to search for groups on ldap://servername", $event->error());
	}

	public function testDiscernNotInAllowedGroup()
	{
		$ldap                                        = new Ldap('ldap-test', 'ldap://username:password@servername/dc=example,dc=com', 'sAMAccountName', 'super');
		$this->mockLdapResource->returnGetAttributes = [
			'memberOf' => ['count' => 1, 'not gonna happen on my watch']
		];
		$event                                       = $this->buildAuthenticateEvent();
		$ldap->discern($event);
		self::assertTrue($event->hasError());
		self::assertStringContainsString("[ldap-test] Not in allowed groups", $event->error());
	}

	public function testDiscernWithGroupSuccess()
	{
		$ldap                                        = new Ldap('ldap-test', 'ldap://username:password@servername/dc=example,dc=com', 'sAMAccountName', 'super');
		$this->mockLdapResource->returnGetAttributes = [
			'memberOf' => [
				'count' => 1,
				'super',
			]
		];
		$event                                       = $this->buildAuthenticateEvent();
		$ldap->discern($event);

		self::assertFalse($event->hasError());
	}

	/**
	 * @dataProvider provideLdapGetAttributeInvalidForMemberOf
	 */
	public function testDiscernByGroupWithAttributeFailureScenarios($payload)
	{
		$ldap                                        = new Ldap('ldap-test', 'ldap://username:password@servername/dc=example,dc=com', 'sAMAccountName', 'super');
		$this->mockLdapResource->returnGetAttributes = $payload;
		$event                                       = $this->buildAuthenticateEvent();
		$ldap->discern($event);

		self::assertTrue($event->hasError());
		self::assertStringContainsString("[ldap-test] Identity has no groups assigned", $event->error());
	}
}
