<?php

namespace Smasty\Extensions;

use Nette,
	Nette\Environment;


/**
 * Smart presenter factory - supports custom name conventions for presenters.
 *
 * @author Martin Srank (http://smasty.net)
 * @license http://opensource.org/licenses/mit-license MIT license
 * @link http://github.com/smasty/nette-extras/
 */
class SmartPresenterFactory extends Nette\Application\PresenterFactory {


	/** @var strng */
	private $baseDir;

	/** @var array */
	private $patterns;


	public function __construct($baseDir, Nette\DI\IContext $context, array $patterns){
		parent::__construct($baseDir, $context);
		$this->baseDir = $baseDir;
		$this->patterns = $patterns;
	}


	/**
	 * Service factory.
	 * @param array $options
	 * @return SmartPresenterFactory
	 */
	public static function register($options){
		if(!$options){
			$options = array();
		}
		foreach($options as $key => $val){
			$options[$key] = iterator_to_array($val);
		}

		$defaults = array(
			'class' => array(
				'prefix' => '',
				'module' => '%sModule\\',
				'presenter' => '%sPresenter'
			),
			'file' => array(
				'prefix' => '',
				'module' => '/%sModule',
				'presenter' => '/presenters/%sPresenter.php'
			)
		);

		$patterns = array();
		foreach($defaults as $key => $val){
			if(isset($options[$key])){
				$patterns[$key] = $options[$key] + $val;
			} else{
				$patterns[$key] = $defaults[$key];
			}
		}
		return new static(
			Environment::getVariable('appDir'),
			Environment::getApplication()->getContext(),
			$patterns
		);
	}


	/**
	 * Formats presenter class name from its name.
	 * @param string $presenter
	 * @return string
	 */
	public function formatPresenterClass($presenter){
		$modules = explode(':', $presenter);
		$presenter = array_pop($modules);
		$s = $this->patterns['class']['prefix'];
		foreach($modules as $module){
			$s .= sprintf($this->patterns['class']['module'], $module);
		}
		$s .= sprintf($this->patterns['class']['presenter'], $presenter);
		return $s;
	}


	/**
	 * Formats presenter name from class name.
	 * @param  string
	 * @return string
	 */
	public function unformatPresenterClass($class){
		$patterns = array();
		foreach($this->patterns['class'] as $k => $v){
			$patterns[$k] = str_replace('%s', "([a-z0-9A-Z]+)", preg_quote($v, '~'));
		}
		$presenter = preg_replace("~$patterns[module]~", "$1:", $class);
		$presenter = preg_replace("~$patterns[presenter]~", "$1", $presenter);
		return substr($presenter, strlen($this->patterns['class']['prefix']));
	}


	/**
	 * Formats presenter class file name.
	 * @param  string
	 * @return string
	 */
	public function formatPresenterFile($presenter){
		$modules = explode(':', $presenter);
		$presenter = array_pop($modules);
		$s = $this->baseDir . $this->patterns['file']['prefix'];
		foreach($modules as $module){
			$s .= sprintf($this->patterns['file']['module'], $module, strtolower($module));
		}
		$s .= sprintf($this->patterns['file']['presenter'], $presenter, strtolower($presenter));
		return $s;
	}


}
