<?php

namespace OpenBoleto\Boleto;

use OpenBoleto\Boleto\Pdf;

abstract class Field 
{
	/**
	 * 
	 * Enter description here ...
	 * 
	 * @var OpenBoleto_Boleto_Pdf
	 */
	protected $_container;
	
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
		return $this->_dataProperty ? $this->_container->getLayout()->get($this->_dataProperty) : null;
	}
	
	public function setContainer(Pdf $container) 
	{
		$this->_container = $container;
		
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
	
	abstract public function draw();
}
