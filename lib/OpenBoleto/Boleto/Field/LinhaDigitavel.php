<?php

namespace OpenBoleto\Boleto\Field;

use OpenBoleto\Boleto\Field;
use OpenBoleto\Boleto\AbstractBoleto;
use ZendPdf\Font;

class LinhaDigitavel extends Field
{	
	protected $_align = 'left';
	
	public function draw() 
	{
		$x1 = $this->_x;
		$y1 = $this->_y;
		$x2 = $this->_x + $this->_width;
		$y2 = $this->_y + $this->_height;
		
		$value   = $this->_boleto->getLayout()->getLinhaDigitavel();
		
		$fontBold = Font::fontWithName(Font::FONT_HELVETICA_BOLD);
		$this->_boleto->setFont($fontBold, 12);
		
		$y1 = $y1+$this->_height-5;
		
		if ($this->_align == 'left') {
			$x1 += 10;
		}
		else {
			$x1 = $x2 - 2 - AbstractBoleto::getTextWidth($value, $fontBold, 12);
		}
		
		$this->_boleto->drawText($value, $x1, AbstractBoleto::translateYPosition($y1), 'UTF-8');
	}
}
