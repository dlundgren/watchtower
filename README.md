WatchTower
==========

WatchTower is a library that allows for the identification and authentication of users using different
backend providers and protocols.

WatchTower recognizes the following state of an identity:

* anonymous
* identified
* authenticated

It should be noted that identified is **NOT** the same thing as being authenticated.

Process
=======

Identify > Authenticate > Validate > Authorize

Currently only Identify and Authenticate is currently working. Validate and Authorize may come later.

Identification and Authentication are implemented from the Sentry interface, which only knows how to discern the events
given to it. The Sentries currently implemented only do one or the other and not both, at this time. As mentioned there
are two events that are fired in the system Identify and Authenticate.

I chose not implement any other major Event Manager as WatchTower was not designed to be a complete event management
system, but the sentries still needed contextual information and events seem most logical at the moment.

That said you attach any type of Sentry using the WatchTower::watch(Sentry) method.

Identification
--------------

Identification happens when called, or directly before Authentication. Transparent identification, that using a
session variable, or IP based, can be obtained by adding on of the stealth sentries to WatchTower first, then adding
further identification sentries. thin two stages.

Authentication
--------------

Both Identity and Credential are required.

Session Support
---------------

WatchTower provides a PhpSession class for native php session storage. It is recommended to create a class that
implements the Sentry interface and interacts with the frameworks session object in a similar fashion as the
PhpSession class does with the native session.

Sentries
========

Transparent Identification
--------------------------

These sentries are used behind the scenes in order to load an identity from the session, or another adapter that may
check for the IP Address range to mark the session as a guest.

Identification
--------------

These sentries are used during the identify() or authenticate() calls to identify the user.

Authentication
--------------

Authentication sentries validate the credentials and identity are valid.

Credits
=======

This is based of the ZetaComponents Authentication components requirements documentation.
https://github.com/zetacomponents/Authentication/blob/master/design/requirements.txt
