<?php

namespace OpenBoleto\Boleto\Field;

use OpenBoleto\Boleto\Field as BaseField;
use OpenBoleto\Boleto\Pdf as BoletoPdf;
use ZendPdf\Font;
use ZendPdf\Color;
use ZendPdf\Page;

class Pdf extends BaseField
{
	/**
	 * 
	 * Enter description here ...
	 * 
	 * @var BoletoPdf
	 */
	protected $_container;
	
	/**
	 * 
	 * Indica se o campo possui bordas
	 * 
	 * @var boolean
	 */
	protected $_border = true;
	
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
			$value = call_user_func($this->_renderer, $value, $this->_container->getLayout());
		}
		
		$fontBold = Font::fontWithName(Font::FONT_HELVETICA_BOLD);
		$fontNormal = Font::fontWithName(Font::FONT_HELVETICA);
		
		if ($this->_border) {
			$this->_container->setLineDashingPattern(Page::LINE_DASHING_SOLID);
			$this->_container->setLineWidth(0.75);
			$this->_container->setLineColor(new Color\GrayScale(0));
			$this->_container->drawRectangle(
				$x1, 
				BoletoPdf::translateYPosition($y1), 
				$x2, 
				BoletoPdf::translateYPosition($y2), 
				Page::SHAPE_DRAW_STROKE
			);
		}
		$this->_container->setFont($fontNormal, 7);
		$this->_container->drawText($this->_label, ($x1+2), BoletoPdf::translateYPosition($y1+8), 'UTF-8');
		$this->_container->setFont($fontBold, 9);
		
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
			$x1 = $x2 - 2 - BoletoPdf::getTextWidth($value, $fontBold, 9);
		}
		
		if ($value && $this->_multiline) {
			$value = explode("\n", $value);
			
			foreach($value as $v) {
				if ($this->_align == 'right') {
					$x1 = $x2 - 2 - BoletoPdf::getTextWidth($v, $fontBold, 9);
				}
				
				$this->_container->drawText($v, $x1, BoletoPdf::translateYPosition($y1), 'UTF-8');
				$y1 += 10;	
			}
		}
		else {
            if (is_object($value)) var_dump($value);
			$this->_container->drawText($value, $x1, BoletoPdf::translateYPosition($y1), 'UTF-8');
		}
	}
}
