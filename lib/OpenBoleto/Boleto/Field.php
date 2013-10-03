<?php

namespace OpenBoleto\Boleto;

use OpenBoleto\Boleto\AbstractBoleto;
use ZendPdf\Font;
use ZendPdf\Color;
use ZendPdf\Page;

class Field 
{
	/**
	 * 
	 * Enter description here ...
	 * 
	 * @var AbstractBoleto
	 */
	protected $_boleto;
	
	/**
	 * 
	 * Nome do campo
	 * 
	 * @var string
	 */
	protected $_name = '';
	
	/**
	 * 
	 * Label do campo
	 * 
	 * @var string
	 */
	protected $_label = '';
	
	/**
	 * 
	 * Propriedade do layout que irá fornecer o valor
	 * 
	 * @var string
	 */
	protected $_dataProperty;
	
	/**
	 * 
	 * Valor atribuído diretamente, quando não $_dataProperty não por necessário
	 * 
	 * @var mixed
	 */
	protected $_value;
	
	/**
	 * 
	 * Posição X
	 * 
	 * @var float
	 */
	protected $_x = 0;
	
	/**
	 * 
	 * Posição Y
	 * 
	 * @var float
	 */
	protected $_y = 0;
	
	/**
	 * 
	 * Tamanho do campo
	 * 
	 * @var float
	 */
	protected $_width = 100;
	
	/**
	 * 
	 * Altura do campo
	 * 
	 * @var float
	 */
	protected $_height = 20;
	
	/**
	 * 
	 * Alinhamento do valor
	 * 
	 * @var string
	 */
	protected $_align = 'left';
	
	/**
	 * Indica se o campo tem múltiplas linhas
	 * 
	 * @var boolean
	 */
	protected $_multiline = false;
	
	/**
	 * 
	 * Callback para tratar o valor do campo
	 * 
	 * @var callback
	 */
	protected $_renderer;
	
	/**
	 * 
	 * Indica se o campo possui bordas
	 * 
	 * @var boolean
	 */
	protected $_border = true;
	
	/**
	 * 
	 * Construtor
	 * 
	 * @param string $name
	 * @param array $options
	 */
	public function __construct($name, array $options = array()) 
	{
		$this->setName($name);
		
		if (count($options) > 0) { 
			$this->setOptions($options);
		}
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param array $options
	 * @throws Exception
	 */
	public function setOptions(array $options) 
	{
		foreach ($options as $option => $value) {
			$setOptionMethod = sprintf('set%s', ucfirst($option));
			
			if (method_exists($this, $setOptionMethod)) {
				call_user_func(array($this, $setOptionMethod), $value);
			}
			else {
				throw new Exception(sprintf("Parâmetro inválido: %s", $option));
			}
		}
	}
	
	public function getDataPropertyValue() 
	{
		return $this->_dataProperty ? $this->_boleto->getLayout()->get($this->_dataProperty) : null;
	}
	
	public function setBoleto(AbstractBoleto $boleto) 
	{
		$this->_boleto = $boleto;
		
		return $this;
	}
	
	public function setName($name) 
	{
		$this->_name = $name;
		
		return $this;
	}
	
	public function getName() 
	{
		return $this->_name;
	}
	
	public function setLabel($label) 
	{
		$this->_label = $label;
		
		return $this;
	}
	
	public function setDataProperty($dataProperty) 
	{
		$this->_dataProperty = $dataProperty;
		
		return $this;
	}
	
	public function setX($x) 
	{
		$this->_x = $x;
		
		return $this;
	}
	
	public function setY($y) 
	{
		$this->_y = $y;
		
		return $this;
	}
	
	public function getY() 
	{
		return $this->_y;
	}
	
	public function setWidth($width) 
	{
		$this->_width = $width;
		
		return $this;
	}
	
	public function setHeight($height) 
	{
		$this->_height = $height;
		
		return $this;
	}
	
	public function setAlign($align) 
	{
		$this->_align = $align;
		
		return $this;
	}
	
	public function setMultiline($multiline) 
	{
		$this->_multiline = (boolean) $multiline;
		
		return $this;
	}
	
	public function setRenderer($callback)
	{
		$this->_renderer = $callback;
		
		return $this;
	}
	
	public function setValue($value)
	{
		$this->_value = $value;
		
		return $this;
	}
	
	public function getValue() 
	{
		return $this->_value;
	}
    
    public function setBorder($border)
	{
		$this->_border = $border;
	}
	
	public function draw() 
	{
		$x1 = $this->_x;
		$y1 = $this->_y;
		$x2 = $this->_x + $this->_width;
		$y2 = $this->_y + $this->_height;
		
		$value = $this->_dataProperty ? $this->getDataPropertyValue() : $this->getValue();
		if (!is_null($this->_renderer) && is_callable($this->_renderer)) {
			$value = call_user_func($this->_renderer, $value, $this->_boleto->getLayout());
		}
		
		$fontBold = Font::fontWithName(Font::FONT_HELVETICA_BOLD);
		$fontNormal = Font::fontWithName(Font::FONT_HELVETICA);
		
		if ($this->_border) {
			$this->_boleto->setLineDashingPattern(Page::LINE_DASHING_SOLID);
			$this->_boleto->setLineWidth(0.75);
			$this->_boleto->setLineColor(new Color\GrayScale(0));
			$this->_boleto->drawRectangle(
				$x1, 
				AbstractBoleto::translateYPosition($y1), 
				$x2, 
				AbstractBoleto::translateYPosition($y2), 
				Page::SHAPE_DRAW_STROKE
			);
		}
		$this->_boleto->setFont($fontNormal, 7);
		$this->_boleto->drawText($this->_label, ($x1+2), AbstractBoleto::translateYPosition($y1+8), 'UTF-8');
		$this->_boleto->setFont($fontBold, 9);
		
		if ($this->_multiline) {
			$y1 = $y1+18;
		}
		else {
			$y1 = $y1+$this->_height-2;
		}
		
		if ($this->_align == 'left') {
			$x1 += 2;
		}
		elseif (false === $this->_multiline) {
			$x1 = $x2 - 2 - AbstractBoleto::getTextWidth($value, $fontBold, 9);
		}
		
		if ($value && $this->_multiline) {
			$value = explode("\n", $value);
			
			foreach($value as $v) {
				if ($this->_align == 'right') {
					$x1 = $x2 - 2 - AbstractBoleto::getTextWidth($v, $fontBold, 9);
				}
				
				$this->_boleto->drawText($v, $x1, AbstractBoleto::translateYPosition($y1), 'UTF-8');
				$y1 += 10;	
			}
		}
		else {
			$this->_boleto->drawText($value, $x1, AbstractBoleto::translateYPosition($y1), 'UTF-8');
		}
	}
}
