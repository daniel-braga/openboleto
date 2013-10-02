<?php

namespace OpenBoleto\Boleto\Field\Pdf;

use OpenBoleto\Boleto\Field\Pdf as PdfField;
use OpenBoleto\Boleto\Pdf as BoletoPdf;
use ZendPdf\Font;
use ZendPdf\Page;
Use ZendPdf\Color;

class CodigoBanco extends PdfField
{	
	public function draw() 
	{
		$x1 = $this->_x;
		$y1 = $this->_y;
		$x2 = $this->_x + $this->_width;
		$y2 = $this->_y + $this->_height;
		
		$fontBold = Font::fontWithName(Font::FONT_HELVETICA_BOLD);
		
		$value = $this->_container->getLayout()->getCodigoBancoComDv();
		
		$this->_container->setLineDashingPattern(Page::LINE_DASHING_SOLID);
		$this->_container->setLineWidth(2);
		$this->_container->setLineColor(new Color\GrayScale(0));
		$this->_container->drawLine($x1, BoletoPdf::translateYPosition($y1), $x1, BoletoPdf::translateYPosition($y2));
		$this->_container->drawLine($x1, BoletoPdf::translateYPosition($y2), $x2, BoletoPdf::translateYPosition($y2));
		
		$this->_container->setFont($fontBold, 17);
		
		$y1 = $y1+$this->_height-5;
		$x1 = $x1 + ($this->_width/2)- (BoletoPdf::getTextWidth($value, $fontBold, 17)/2);
		
		$this->_container->drawText($value, $x1, BoletoPdf::translateYPosition($y1), 'UTF-8');
	}
}
