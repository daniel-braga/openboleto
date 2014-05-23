<?php
ini_set('display_errors', 'on');
require_once '../vendor/autoload.php';

use OpenBoleto\BoletoFactory;

$dataVencimento = \DateTime::createFromFormat('d/m/Y', '23/10/2013');

$layoutParams = array(
	'especieDocumento' => 'DM',
	'nossoNumero' => '3',
    'agencia' => '8044',
    'conta' => '1623',
    'dacConta' => '9',
	'carteira' => '109',
	'numeroDocumento' => '43075',
    'valorDocumento' => 16855.92,
	'dataVencimento' => $dataVencimento,
    'codigoBeneficiario' => '',
	'dataDocumento' => \DateTime::createFromFormat('d/m/Y', '08/10/2013'),
	'dataProcessamento' => \DateTime::createFromFormat('d/m/Y', '08/10/2013'),
	'nomePagador' => 'S6-SHIBATA EMPORIO LTDA.',
	'cpfCnpjPagador' => '10.432.515/0001-14',
	'enderecoPagador' => 'RUA OLEGARIO PAIVA - CENTRO - MOGI DAS CRUZES - SP - 08780-040',
	'demonstrativo' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890uvwxyzABCDEFGHIJKLMNOPQRSTU',
    'aceite' => 'N',
	'instrucao' => '',
	'cpfCnpjBeneficiario' => '00.185.632/0004-05',
	'enderecoBeneficiario' => 'AV CAVALHEIRO NAMI JAFET 343 VILA INDUSTRIAL MOGI DAS CRUZES SP 08770-040',
	'cidadeBeneficiario' => '',
	'estadoBeneficiario' => '',
	'beneficiario' => 'COMERCIAL OSVALDO TARORA LTDA',
    'nomeSacadorAvalista' => '',
    'cnpjSacadorAvalista' => ''
);

$boleto = BoletoFactory::createFactory('itau', $layoutParams, true);
$result = $boleto->output();

file_put_contents('../tmp/boleto_local.pdf', $result);