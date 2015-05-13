<?php
/**
 * WatchTower Authentication system
 *
 * @license   MIT License
 * @author    David Lundgren
 * @link      http://dlundgren.github.io/watchtower
 * @copyright 2015. David Lundgren
 */

/**
 * This is a basic example of how to use WatchTower
 */
require_once __DIR__ . '/../vendor/autoload.php';

use WatchTower\WatchTower;
use WatchTower\Event\Event;

$wt = new WatchTower();
$users = new \WatchTower\Sentry\Identification\InMemory('id');
$users->add('superman', (object)['name' => 'Khalel', 'alias' => 'clark kent']);
$wt->watch($users);
$wt->watch(new \WatchTower\Sentry\Authentication\Callback('auth', function(Event $event) {
	$identity = $event->identity();
	if ($identity->isIdentified()) {
		$cred = $identity->credential();
		if (!empty($cred) && $cred === 'password') {
			return;
		}

		$event->triggerError(\WatchTower\Sentry\Sentry::INVALID, "Invalid username or password");
	}
}));

// fails authentication and identification
$identity = $wt->authenticate("bizarro", 'forever man');
if ($identity->isIdentified() === false) {
	echo 'Failure: ' . join(', ', $identity->getErrors()) . "<br>\n";
}
// fails authentication, but identification is succesful
$identity = $wt->authenticate("superman", "the ice fortress");
if ($identity->isAuthenticated() === false) {
	echo 'Failure: ' . join(', ', $identity->getErrors()) . "<br>\n";
}

// passed
$identity = $wt->authenticate("superman", "password");
if ($identity->isAuthenticated()) {
	echo "Success! Welcome {$identity->identified()->alias}.<br>\n";
}
