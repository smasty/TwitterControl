<?php
/**
 * Copyright 2011 Martin Srank (http://smasty.net)
 */

namespace Smasty\Components;

use Nette;


/**
 * Navigation control for Nette framework.
 * @author Martin Srank (http://smasty.net)
 * @license http://opensource.org/licenses/mit-license MIT license
 * @link http://github.com/smasty/nette-extras/
 */
class NavigationControl extends \Nette\Application\UI\Control {


	/** @var NavigationNode */
	private $currentNode;


	/**
	 * Render full menu tree.
	 * @param bool $renderTree
	 * @return void
	 */
	public function render($renderTree = true){
		if($this->hasChildren()){
			$this->template->children = $this->getComponents();
		}
		$this->template->renderTree = $renderTree;
		$this->template->setFile(__DIR__ . '/Navigation.latte');
		$this->template->render();
	}


	/**
	 * Render one-level menu.
	 * @return void
	 */
	public function renderMenu(){
		$this->render(false);
	}


	/**
	 * Render breadcrumbs.
	 * @return void
	 */
	public function renderBreadCrumbs(){
		if($this->hasChildren() && !$this->isNode()){
			$this->template->setFile(__DIR__ . '/Navigation.breadcrumbs.latte');
			$this->template->render();
		}
	}


	/**
	 * Add new navigation node as child.
	 * @param string $label
	 * @param string $link
	 * @return NavigationNode
	 */
	public function add($label, $link){
		static $counter;
		$node = new NavigationNode($label, $link);
		$this->addComponent($node, ++$counter);
		return $node;
	}


	/**
	 * Check if node/navigation has children.
	 * @return bool
	 */
	public function hasChildren(){
		return $this->getComponents()->count() > 0;
	}


	/**
	 * Is current instance a node?
	 * @return bool
	 */
	public function isNode(){
		return $this instanceof NavigationNode;
	}


	/**
	 * Set given node as current.
	 * @param NavigationNode $node
	 */
	public function setCurrent(NavigationNode $node){
		if($this->currentNode instanceof NavigationNode){
			$this->currentNode->isCurrent = false;
		}
		$node->isCurrent = true;
		$this->currentNode = $node;
	}


}


/**
 * Navigation node.
 * @author Martin Srank (http://smasty.net)
 * @license http://opensource.org/licenses/mit-license MIT license
 * @link http://github.com/smasty/nette-extras/
 *
 * @property-read string $link
 * @property-read string $label
 */
class NavigationNode extends NavigationControl {


	/** @var bool */
	public $isCurrent = false;

	/** @var string */
	private $link;

	/** @var string */
	private $label;


	/**
	 * Create navigation node.
	 * @param string $label
	 * @param string $link
	 */
	public function __construct($label, $link){
		parent::__construct();

		$this->label = $label;
		$this->link = $link;
	}


	/**
	 * Get the link.
	 * @return string
	 */
	public function getLink(){
		return $this->link;
	}


	/**
	 * Get the label.
	 * @return string
	 */
	public function getLabel(){
		return $this->label;
	}


}