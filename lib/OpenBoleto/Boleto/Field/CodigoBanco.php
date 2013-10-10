<?php

namespace OpenBoleto\Boleto\Field;

use OpenBoleto\Boleto\Field;
use OpenBoleto\Boleto\AbstractBoleto;
use ZendPdf\Font;
use ZendPdf\Page;
Use ZendPdf\Color;

class CodigoBanco extends Field
{	
	public function draw() 
	{
		$x1 = $this->_x;
		$y1 = $this->_y;
		$x2 = $this->_x + $this->_width;
		$y2 = $this->_y + $this->_height;
		
		$value = $this->_boleto->getLayout()->getCodigoBancoComDv();
		
		$this->_boleto->setLineDashingPattern(Page::LINE_DASHING_SOLID);
		$this->_boleto->setLineWidth(2);
		$this->_boleto->setLineColor(new Color\GrayScale(0));
		$this->_boleto->drawLine($x1, AbstractBoleto::translateYPosition($y1), $x1, AbstractBoleto::translateYPosition($y2));
        $this->_boleto->drawLine($x2, AbstractBoleto::translateYPosition($y1), $x2, AbstractBoleto::translateYPosition($y2));
		
        $fontBold = Font::fontWithName(Font::FONT_HELVETICA_BOLD);
		$this->_boleto->setFont($fontBold, 16);
		
		$y1 = $y1+$this->_height-5;
		$x1 = $x1 + ($this->_width/2)- (AbstractBoleto::getTextWidth($value, $fontBold, 16)/2);
		
		$this->_boleto->drawText($value, $x1, AbstractBoleto::translateYPosition($y1), 'UTF-8');
	}
}
