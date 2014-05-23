<?php

namespace OpenBoleto\Boleto\Field;

use OpenBoleto\Boleto\Field;
use OpenBoleto\Boleto\AbstractBoleto;
use Zend\Barcode\Barcode as ZendBarcode;
use ZendPdf\Image;

class Barcode extends Field
{
    protected $_tmpDir;
    
    public function __construct($name, array $options = array())
    {
        parent::__construct($name, $options);
        
        $tmpDir = realpath(sprintf('%s/../../../../tmp', dirname(__FILE__)));
        $this->_tmpDir = $tmpDir;
    }
    
	public function draw() 
	{
		$x1 = $this->_x+2;
		$y2 = $this->_y+2;
		
		$barcode = $this->_boleto->getLayout()->getCodigoBarra();
		$imageResource = ZendBarcode::draw(
			'code25interleaved', 'image', array(
				'text' => $barcode,
				'drawText' => false,
				'withQuietZones' => false,
				'mandatoryQuietZones' => false
			), array(
				'imageType' => 'jpeg',
				'height' => 50,
				'width' => 405
			)
		);
        
 		$tempFileName = sprintf('%s/%s.jpg', $this->_tmpDir, $barcode);
		if (false === imagejpeg($imageResource, $tempFileName)) {
			throw new \Exception(sprintf("Não foi possível criar o arquivo %s", $tempFileName));
		}
        
		$pdfImageResource = Image::imageWithPath($tempFileName);
		$barcodeImageHeight = ($pdfImageResource->getPixelHeight()/96) * 72; // pixels to points at 96dpi
		$barcodeImageWidth = ($pdfImageResource->getPixelWidth()/96) * 72; // pixels to points at 96dpi
		
		$x2 = $x1+$barcodeImageWidth;
		$y1 = AbstractBoleto::translateYPosition($y2+$barcodeImageHeight);
		$y2 = AbstractBoleto::translateYPosition($y2);
		
		$this->_boleto->drawImage($pdfImageResource, $x1, $y1, $x2, $y2);
        
        unlink($tempFileName);
	}
}
