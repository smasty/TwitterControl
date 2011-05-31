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
 * Provides Nette debugBar panel with info about performed queries.
 */
class NeevoPanel implements INeevoObserver, Nette\Diagnostics\IBarPanel {


	public static $templateFile = '/NeevoPanel.latte';

	/** @var array */
	private $tickets = array();

	/** @var int */
	private $numQueries = 0;

	/** @var int */
	private $totalTime = 0;


	/**
	 * Register new Nette debugBar panel.
	 * @param Neevo $neevo
	 */
	public static function register(Neevo $neevo){
		$panel = new self;

		$neevo->attachObserver($panel, true);

		Nette\Diagnostics\Debugger::$bar->addPanel($panel);
		Nette\Diagnostics\Debugger::$blueScreen->addPanel(callback($panel, 'renderException'), __CLASS__);
	}


	/**
	 * Receives update from observable.
	 * @param INeevoObservable $observable
	 * @param int $event
	 */
	public function updateStatus(INeevoObservable $observable, $event){
		if($event & INeevoObserver::QUERY){
			$statement = func_get_arg(2);
			$this->numQueries++;
			$this->totalTime += $statement->getTime();

			if($statement instanceof NeevoResult){
				try{
					$rows = count($statement);
				} catch(Exception $e){
					$rows = '?';
				}
			} else{
				$rows = '-';
			}

			$source = null;
			foreach(debug_backtrace(false) as $t){
				if(isset($t['file']) && strpos($t['file'], realpath(__DIR__ . '/../')) !== 0){
					$source = array($t['file'], (int) $t['line']);
					break;
				}
			}

			$this->tickets[] = array(
				'sql' => $statement->__toString(),
				'time' => $statement->getTime(),
				'rows' => $rows,
				'source' => $source,
				'connection' => $observable
			);
		}
	}


	/**
	 * Renders SQL query string to Nette debug bluescreen when available.
	 * @param NeevoException $e
	 */
	public function renderException($e){
		if($e instanceof NeevoException && $e->getSql()){
			return array(
				'tab' => 'SQL',
				'panel' => Neevo::highlightSql($e->getSql())
			);
		}
	}


	/**
	 * Renders Nette debugBar tab.
	 * @return string
	 */
	public function getTab(){
		return '<span title="Neevo database layer - rev. #' . Neevo::REVISION . '">'
		. '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAEYSURBVBgZBcHPio5hGAfg6/2+R980k6wmJgsJ5U/ZOAqbSc2GnXOwUg7BESgLUeIQ1GSjLFnMwsKGGg1qxJRmPM97/1zXFAAAAEADdlfZzr26miup2svnelq7d2aYgt3rebl585wN6+K3I1/9fJe7O/uIePP2SypJkiRJ0vMhr55FLCA3zgIAOK9uQ4MS361ZOSX+OrTvkgINSjS/HIvhjxNNFGgQsbSmabohKDNoUGLohsls6BaiQIMSs2FYmnXdUsygQYmumy3Nhi6igwalDEOJEjPKP7CA2aFNK8Bkyy3fdNCg7r9/fW3jgpVJbDmy5+PB2IYp4MXFelQ7izPrhkPHB+P5/PjhD5gCgCenx+VR/dODEwD+A3T7nqbxwf1HAAAAAElFTkSuQmCC" width="16" height="16">'
		. ($this->numQueries ? $this->numQueries : 'No') . ' queries'
		. ($this->totalTime ? ' / ' . sprintf('%0.1f', $this->totalTime * 1000) . ' ms' : '')
		. '</span>';
	}


	/**
	 * Renders Nette debugBar panel.
	 * @return string
	 */
	public function getPanel(){
		if(!$this->numQueries){
			return '';
		}

		$template = new Nette\Templating\FileTemplate(__DIR__ . self::$templateFile);
		$template->registerFilter(new Nette\Latte\Engine);

		$template->registerHelper('time', function($time){
			return sprintf('%0.3f', $time * 1000);
		});
		$template->registerHelper('editorLink', function($source){
			return strtr(Nette\Diagnostics\Debugger::$editor,
				array('%file' => rawurlencode($source[0]), '%line' => $source[1]));
		});
		$template->registerHelper('source', function($source){
			return basename(dirname($source[0])) . '/' . basename($source[0]) . ":$source[1]";
		});
		$template->registerHelper('sql', function($sql, $len = 1000){
			if(strlen($sql) > $len){
				$sql = substr($sql, 0, $len) . "\xE2\x80\xA6";
			}
			return Neevo::highlightSql($sql);
		});

		$template->tickets = $this->tickets;
		$template->totalTime = $this->totalTime;
		$template->numQueries = $this->numQueries;

		return $template->__toString();
	}


	private function __construct(){

	}


}