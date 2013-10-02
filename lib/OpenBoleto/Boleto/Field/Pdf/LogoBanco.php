<?php

namespace OpenBoleto\Boleto\Field\Pdf;

use OpenBoleto\Boleto\Field\Pdf\Imagem as ImagemField;
use OpenBoleto\Boleto\Pdf as BoletoPdf;
use ZendPdf\Page;
use ZendPdf\Color;

class LogoBanco extends ImagemField
{
	public function draw() 
	{
		parent::draw();

		$x1 = $this->_x;
		$y1 = $this->_y;
		$x2 = $this->_x + $this->_width;
		$y2 = $this->_y + $this->_height;
		
		$this->_container->setLineDashingPattern(Page::LINE_DASHING_SOLID);
		$this->_container->setLineWidth(2);
		$this->_container->setLineColor(new Color\GrayScale(0));
		$this->_container->drawLine($x1, BoletoPdf::translateYPosition($y2), $x2, BoletoPdf::translateYPosition($y2));

	}
}
