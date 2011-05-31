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
 * Neevo cache using Nette Framework cache.
 * @author Martin Srank
 * @package NeevoCache
 */
class NeevoCacheNette implements INeevoCache {


	/** @var string */
	public static $cacheKey = 'Neevo.Cache';

	/** @var Nette\Caching\Cache */
	private $cache;


	public function __construct(\Nette\DI\IContainer $context){
		$this->cache = new Nette\Caching\Cache($context->cacheStorage, self::$cacheKey);
	}


	public function fetch($key){
		return $this->cache[$key];
	}


	public function store($key, $value){
		$this->cache[$key] = $value;
	}


	public function flush(){
		$this->cache->clean();
		return true;
	}


}
