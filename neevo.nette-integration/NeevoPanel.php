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
	Nette\Diagnostics\Helpers,
	Nette\Diagnostics\BlueScreen,
	Nette\Diagnostics\IBarPanel,
	Nette\Templating\FileTemplate,
	Nette\Latte\Engine,
	Nette\Utils\Html;


/**
 * Provides Nette debugBar panel with info about performed queries.
 */
class NeevoPanel implements INeevoObserver, IBarPanel {


	public static $templateFile = '/NeevoPanel.latte';

	/** @var array */
	private $tickets = array();

	/** @var int */
	private $numQueries = 0;

	/** @var int */
	private $totalTime = 0;

	/** @var array */
	private $failedQuerySource;

	/** @var bool */
	private $explain = true;


	public function __construct(array $options){
		if(isset($options['explain'])){
			$this->explain = $options['explain'];
		}
	}


	/**
	 * Receives update from observable.
	 * @param INeevoObservable $observable
	 * @param int $event
	 */
	public function updateStatus(INeevoObservable $observable, $event){
		$source = null;
		foreach(debug_backtrace(false) as $t){
			if(isset($t['file']) && strpos($t['file'], realpath(__DIR__ . '/../')) !== 0){
				$source = array($t['file'], (int) $t['line']);
				break;
			}
		}

		if($event & INeevoObserver::QUERY){
			$this->numQueries++;
			$this->totalTime += $observable->getTime();

			if($observable instanceof NeevoResult){
				try{
					$rows = count($observable);
				} catch(Exception $e){
					$rows = '?';
				}
				$explain = $this->explain ? $observable->explain() : null;
			} else{
				$rows = '-';
			}

			$this->tickets[] = array(
				'sql' => $observable->__toString(),
				'time' => $observable->getTime(),
				'rows' => $rows,
				'source' => $source,
				'connection' => $observable->getConnection(),
				'explain' => isset($explain) ? $explain : null
			);
		} elseif($event === INeevoObserver::EXCEPTION){
			$this->failedQuerySource = $source;
		}
	}


	/**
	 * Renders SQL query string to Nette debug bluescreen when available.
	 * @param NeevoException $e
	 */
	public function renderException($e){
		if($e instanceof NeevoException && $e->getSql()){
			list($file, $line) = $this->failedQuerySource;
			return array(
				'tab' => 'SQL',
				'panel' => Neevo::highlightSql($e->getSql())
				. '<p><b>File:</b> '
				. Helpers::editorLink($file, $line)
				. " &nbsp; <b>Line:</b> $line</p>"
				. (is_file($file) ? '<pre>' . BlueScreen::highlightFile($file, $line) . '</pre>' : '')
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

		$template = new FileTemplate(__DIR__ . self::$templateFile);
		$template->registerFilter(new Engine);
		$template->registerHelper('time', function($time){
			return sprintf('%0.3f', $time * 1000);
		});
		$template->registerHelper('sql', callback('Neevo', 'highlightSql'));
		$template->registerHelper('sourceLink', function($source){
			$el = Html::el('a');
			$el->class = 'link';
			$el->href(strtr(Debugger::$editor,
				array('%file' => rawurlencode($source[0]), '%line' => $source[1])
			));
			$el->setText(basename(dirname($source[0])) . '/' . basename($source[0]) . ":$source[1]");
			$el->title = implode(':', $source);

			return $el;
		});
		$template->registerHelper('explain', function($ticket){
			return $ticket['sql'];
		});

		$template->tickets = $this->tickets;
		$template->totalTime = $this->totalTime;
		$template->numQueries = $this->numQueries;

		return $template;
	}


	/**
	 * Get file and line where the query was executed.
	 * @return array
	 */
	private function getQuerySource(){
		$source = null;
		foreach(debug_backtrace(false) as $t){
			if(isset($t['file']) && strpos($t['file'], realpath(__DIR__ . '/../')) !== 0){
				$source = array($t['file'], (int) $t['line']);
				break;
			}
		}
		return $source;
	}


}