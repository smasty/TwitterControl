This package is a part of Neevo - Tiny database layer for PHP,
subject to the MIT license (http://opensource.org/licenses/mit-license).

This package provides tools to optimize experience when using
Neevo along with Nette Framework. It registers a new Nette debugBar
panel showing queries performed by Neevo, registers Neevo as
a Nette DI Container service so it's globally available and
adds a tab to Nette Bluescreen with SQL query if it failed.

Only Nette Framework 2.0 beta and above PHP 5.3 packages are supported.

Instructions
============

1.  Copy this directory to your Neevo directory. Assuming that neevo.php
    is located in %libsDir%/Neevo/neevo.php, this should be
    %libsDir%/Neevo/nette-integration/.

2.  In your Nette Framework config file (e.g. %appDir%/config.neon),
    in "services" section, add the following service definition:

    services:
        ...
        neevo:
			factory: NeevoFactory::create
			arguments: [%database%, explain: yes]

	'explain' option denotes whether or not you want to run EXPLAIN on all
	performed SELECT queries for debugging purposes. Defaults to 'yes'.


3.  In the same config file, add another section called "database".
    That is the place for all your Neevo configuration, e.g.

    database:
        driver: MySQLi
        username: root
        password: ****
        database: my_database