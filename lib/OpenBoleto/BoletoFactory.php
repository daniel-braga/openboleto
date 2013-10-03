<?php
namespace OpenBoleto;

class BoletoFactory
{
	public static function getRootDir()
    {
		return dirname(realpath(__FILE__));
	}
    
    public static function createFactory($layout, array $layoutParams = array()) 
	{
        $boletoClass    = sprintf('%s\Boleto\%s', __NAMESPACE__, ucfirst($layout));
		$layoutClass    = sprintf('%s\Layout\%s', __NAMESPACE__, ucfirst($layout));
        
        $layoutObj = new $layoutClass($layoutParams);
		$boletoObj = new $boletoClass($layoutObj);
        
        return $boletoObj;
	}
}