<?php
/**
 * Neevo - Tiny database layer for PHP. (http://neevo.smasty.net)
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file license.txt.
 *
 * Copyright (c) 2011 Martin Srank (http://smasty.net)
 *
 */
use Nette\Diagnostics\Debugger,
	Nette\DI\Container;


/**
 * Nette Framework service for using Neevo.
 */
class NeevoService {


	/** @var string */
	public static $configKey = 'database';


	/**
	 * Neevo service factory.
	 * @param Nette\DI\Container $context
	 * @return Neevo
	 */
	public static function create(Container $context, array $options){
		$neevo = new Neevo($context->params[self::$configKey], new NeevoCacheNette($context));

		// Register debugBar panel
		$panel = new NeevoPanel($options);
		$neevo->attachObserver($panel, NeevoPanel::QUERY + NeevoPanel::EXCEPTION);
		Debugger::$bar->addPanel($panel);

		// Register Bluescreen panel
		Debugger::$blueScreen->addPanel(callback($panel, 'renderException'), get_class($panel));

		return $neevo;
	}


}
