<?php
namespace Blacktrue\Validacion;

use DOMDocument;
use InvalidArgumentException;

class Schema{

    /**
     * @var $xml string
     */
    protected $xml;

    /**
     * @var $xsd string
     */
    protected $xsd;

    /**
     * @var $errors array
     */
    protected $errors = [];

    /**
     * @var $valid bool
     */
    protected $valid = true;

    public function __construct()
    {
        libxml_use_internal_errors(true);
    }

    /**
     * @return mixed
     */
    public function isValid()
    {
        return $this->valid;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return string
     */
    public function getXsd()
    {
        return $this->xsd;
    }

    /**
     * @param string $xsd
     * @return Schema
     */
    public function setXsd($xsd)
    {
        if(!file_exists($xsd))
        {
            throw new InvalidArgumentException("El XSD proporcionado no existe");
        }
        $this->xsd = $xsd;
        return $this;
    }

    /**
     * @return string
     */
    public function getXml()
    {
        return $this->xml;
    }

    /**
     * @param string $xml
     * @return Schema
     */
    public function setXml($xml)
    {
        if(empty($xml))
        {
            throw new InvalidArgumentException("Valor XML Invalido");
        }
        $this->xml = $xml;
        return $this;
    }

    public function validar()
    {
        $xml = new DOMDocument();
        $xml->loadXML($this->getXml());
        if(!$xml->schemaValidate($this->getXsd()))
        {
            $this->valid = false;
            $this->errors = libxml_get_errors();
        }
        return $this;
    }


}