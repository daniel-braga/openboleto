<?php

namespace OpenBoleto\Boleto\Field;

use OpenBoleto\Boleto\Field;
use OpenBoleto\Boleto\AbstractBoleto;
use ZendPdf\Font;

class NomeBanco extends Field
{	
	public function draw() 
	{
		$x1 = $this->_x;
		$y1 = $this->_y;
		$x2 = $this->_x + $this->_width;
		$y2 = $this->_y + $this->_height;
		
		$value = $this->_dataProperty ? $this->getDataPropertyValue() : $this->getValue();
		
        $font = Font::fontWithName(Font::FONT_HELVETICA);
		$this->_boleto->setFont($font, 10);
		
		$y1 = $y1+($this->_height/2);
		$x1 = $x1 + ($this->_width/2)- (AbstractBoleto::getTextWidth($value, $font, 10)/2);
		
		$this->_boleto->drawText($value, $x1, AbstractBoleto::translateYPosition($y1), 'UTF-8');
	}
}
