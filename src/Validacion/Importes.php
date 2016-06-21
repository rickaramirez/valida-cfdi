<?php
/**
 * Created by PhpStorm.
 * User: cesar
 * Date: 16/06/16
 * Time: 08:37 AM
 */

namespace Blacktrue\Validacion;

use DOMDocument;
use InvalidArgumentException;

class Importes
{
    /**
     * @var $xml string
     */
    protected $xml;

    /**
     * @var $epilson float
     */
    public $epsilon = 0.00001;

    /**
     * @var $errors array
     */
    protected $errors = [];

    /**
     * @var $valid bool
     */
    protected $valid = true;


    private function reset()
    {
    	$this->errors = [];
    	$this->valid = true;
    }

    /**
     * @return string
     */
    public function getXml()
    {
        return $this->xml;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->valid;
    }

    /**
     * @param string $xml
     */
    public function setXml($xml)
    {
        if(empty($xml))
        {
            throw new InvalidArgumentException("EL valor de XML es invalido");
        }
        $this->reset();
        $this->xml = $xml;
    }

    /**
     * @return $this
     */
    public function validarConceptos()
    {
        $xml = new DOMDocument;
        $xml->loadXML($this->getXml());
        foreach ($xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Concepto') as $element)
        {
            $valorUnitario = floatval($element->getAttribute('valorUnitario'));
            $cantidad = floatval($element->getAttribute('cantidad'));
            $importe = floatval($element->getAttribute('importe'));
            $descripcion = (string)$element->getAttribute('descripcion');
            $_importe = floatval(($valorUnitario * $cantidad));
            if (abs($importe - $_importe) > $this->epsilon)
            {
                $this->valid = false;
                $this->errors[] = "El concepto con descripcion [{$descripcion}] presenta error en el importe {$_importe} | {$importe}";
            }
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function validarSubTotal()
    {
        $xml = new DOMDocument;
        $xml->loadXML($this->getXml());
        $comprobante = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3','Comprobante');
        $subTotal = $comprobante[0]->getAttribute('subTotal');
        $totalConceptos = 0;
        foreach ($xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Concepto') as $element)
        {
            $importe = floatval($element->getAttribute('importe'));
            $totalConceptos += $importe;
        }
        if(abs($totalConceptos-$subTotal) > $this->epsilon)
        {
            $this->valid = false;
            $this->errors[] = "El total de los conceptos no coincide con el subtotal de la factura {$totalConceptos} | {$subTotal}";
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function validarTotal()
    {
        $xml = new DOMDocument;
        $xml->loadXML($this->getXml());
        $comprobante = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3','Comprobante');
        $subTotal = (float)$comprobante[0]->getAttribute('subTotal');
        $descuento = (float)$comprobante[0]->getAttribute('descuento');
        $total = (float)$comprobante[0]->getAttribute('total');
        $impuestos = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3','Impuestos');
        $trasladados = (float)$impuestos[0]->getAttribute('totalImpuestosTrasladados');
        $retenidos = (float)$impuestos[0]->getAttribute('totalImpuestosRetenidos');
        $impuestos_locales = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/implocal','ImpuestosLocales');
        $local_traslado = 0;
        $local_retenido = 0;
        if(isset($impuestos_locales[0]))
        {
            $local_traslado = (float)$impuestos_locales[0]->getAttribute('TotaldeTraslados');
            $local_retenido = (float)$impuestos_locales[0]->getAttribute('TotaldeRetenciones');
        }
        $totalImpuestos = ($retenidos+$trasladados);
        $totalLocales = ($local_traslado+$local_retenido);        
        $totalCalculado = ($subTotal-$descuento)+$totalImpuestos+$totalLocales;
        if(abs($totalCalculado-$total) > $this->epsilon)
        {
            $this->valid = false;
            $this->errors[] = "La suma del subtotal - descuentos + impuestos no coincide con el total de la factura {$totalCalculado} | {$total}";
        }
        return $this;
    }
}