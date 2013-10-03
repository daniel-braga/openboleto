<?php

namespace OpenBoleto\Boleto\Field;

use OpenBoleto\Boleto\Field\Imagem as ImagemField;
use OpenBoleto\Boleto\AbstractBoleto;
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
		
		$this->_boleto->setLineDashingPattern(Page::LINE_DASHING_SOLID);
		$this->_boleto->setLineWidth(2);
		$this->_boleto->setLineColor(new Color\GrayScale(0));
		$this->_boleto->drawLine($x1, AbstractBoleto::translateYPosition($y2), $x2, AbstractBoleto::translateYPosition($y2));

	}
}
