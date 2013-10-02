<?php

namespace OpenBoleto\Container;

interface ContainerInterface {
	
	public function add($content);
	
	public function output(array $options = array());
}