<?php

namespace OpenBoleto\Container;

use ZendPdf\PdfDocument;

class Pdf extends PdfDocument implements ContainerInterface {
	
	/**
	 * 
	 * Construtor
	 */
	public function __construct() 
	{
		parent::__construct();
	}
	
	/**
	 * 
	 * Enter description here ...
	 * 
	 * @param OpenBoleto\Boleto\Pdf $content
	 */
	public function add($content) 
	{
		$content->init();
		$content->draw();		
		$this->pages[] = $content;
	}
	
	public function output(array $options = array()) 
	{
		$this->save('/Users/daniel/output.pdf');
	}
}