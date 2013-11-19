<?php
include '../vendor/autoload.php';

use OpenBoleto\BoletoFactory;

$dataVencimento = DateTime::createFromFormat('d/m/Y', '23/10/2013');

$layoutParams = array(
	'especieDocumento' => 'DM',
	'nossoNumero' => '3',
    'agencia' => '8044',
    'conta' => '01623',
    'dacConta' => '9',
	'carteira' => '109',
	'numeroDocumento' => '43075',
    'valorDocumento' => 16855.92,
	'dataVencimento' => $dataVencimento,
    'codigoBeneficiario' => '',
	'dataDocumento' => DateTime::createFromFormat('d/m/Y', '08/10/2013'),
	'dataProcessamento' => DateTime::createFromFormat('d/m/Y', '08/10/2013'),
	'nomePagador' => 'S6-SHIBATA EMPORIO LTDA.',
	'cpfCnpjPagador' => '10.432.515/0001-14',
	'enderecoPagador' => "RUA OLEGARIO PAIVA - CENTRO - MOGI DAS CRUZES - SP - 08780-040",
	'demonstrativo' => "",
    'aceite' => 'N',
	'instrucao' => "",
	'cpfCnpjBeneficiario' => '00.185.632/0004-05',
	'enderecoBeneficiario' => 'AV CAVALHEIRO NAMI JAFET 343 VILA INDUSTRIAL MOGI DAS CRUZES SP 08770-040',
	'cidadeBeneficiario' => '',
	'estadoBeneficiario' => '',
	'beneficiario' => 'COMERCIAL OSVALDO TARORA LTDA',
    'nomeSacadorAvalista' => '',
    'cnpjSacadorAvalista' => ''
);

$xml = "<boleto>\n";
foreach($layoutParams as $param => $value) {
    $type = 'string';
    if ($value instanceof \DateTime) {
        $type = 'date';
        $value = $value->format('Y-m-d');
    }
    elseif (is_float($value)) {
        $type = 'float';
    }
    
    $xml .= sprintf("\t<%1\$s type=\"%2\$s\">%3\$s</%1\$s>\n", $param, $type, $value);
}
$xml .= "</boleto>\n";

$client = new SoapClient(null, array(
    'location'   => 'https://site.local/webservices/Boleto.php',
    'uri'        => 'https://site.local/webservices/Boleto.php',
    'local_cert' => '/Users/daniel/cert.pem',
    'trace'      => true,
    'passphrase' => '06286dba'
));

$result = $client->gerar('itau', $xml);

file_put_contents('boleto.pdf', base64_decode($result));
