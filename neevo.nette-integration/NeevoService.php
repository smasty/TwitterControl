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


/**
 * Nette Framework service for using Neevo.
 */
class NeevoService {


	public static $configKey = 'database';


	/**
	 * Neevo service factory.
	 * @param Nette\DI\Container $context
	 * @return Neevo
	 */
	public static function create(Nette\DI\Container $context){
		$neevo = new Neevo($context->params[self::$configKey], new NeevoCacheNette($context));
		NeevoPanel::register($neevo);
		return $neevo;
	}


}
