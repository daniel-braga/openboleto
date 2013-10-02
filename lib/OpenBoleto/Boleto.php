<?php
namespace OpenBoleto;

class Boleto {
	
	public static function getRootDir()
    {
		return dirname(realpath(__FILE__));
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param string $container
	 * @param string $layout
	 * @param array $layoutParams
	 * @return OpenBoleto\Container\Interface
	 */
	public static function factory($container, $layout, array $layoutParams = array()) 
    {
		$containerClass = sprintf('%s\Container\%s', __NAMESPACE__, ucfirst($container));
		$boletoClass    = sprintf('%s\Boleto\%s\%s', __NAMESPACE__, ucfirst($layout), ucfirst($container));
		$layoutClass    = sprintf('%s\Layout\%s',    __NAMESPACE__, ucfirst($layout));
		
		$layoutObj = new $layoutClass($layoutParams);
		$boletoObj = new $boletoClass($layoutObj);
		$containerObj = new $containerClass();
		
		$containerObj->add($boletoObj);
		
		return $containerObj;
	}	
}