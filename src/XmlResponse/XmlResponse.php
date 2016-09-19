<?php

namespace XmlResponse;

use Illuminate\Support\Facades\Response;
use XmlResponse\Exception\XmlResponseException;
use Illuminate\Container\Container;

/**
 * Class XmlResponse
 * @package XmlResponse
 */
class XmlResponse
{
    /**
     * @var Container
     */
    private $container;

    /**
     * XmlResponse constructor.
     */
    public function __construct()
    {
        $this->container = new Container();
    }

    /**
     * @return mixed
     */
    public function app()
    {
        return $this->container->getInstance()->make('config');
    }

    /**
     * @return mixed
     */
    private function header()
    {
        $header['Content-Type'] = 'application/xml';
        return $header;
    }

    /**
     * @param $value
     * @return bool
     */
    private function isType($value)
    {
        return in_array($value, [
            'model',
            'collection',
            'array',
            'object'
        ]);
    }

    /**
     * @param $value
     * @return mixed
     */
    private function caseSensitive($value)
    {
        $caseSensitive = $this->app()->get('xml.caseSensitive');

        if ($caseSensitive){
            $value = explode('_', $value);
            $value = lcfirst(join('', array_map("ucfirst", $value)));
        }
        return $value;
    }


    /**
     * @param array $attribute
     * @param \SimpleXMLElement $xml
     * @throws XmlResponseException
     */
    private function addAttribute($attribute = [], \SimpleXMLElement $xml)
    {
        if (!is_array($attribute)){
            throw new XmlResponseException('Attribute in the header is not an array.');
        }

        foreach ($attribute as $key => $value){
            $xml->addAttribute($key, $value);
        }
    }

    /**
     * @param $array
     * @param bool $xml
     * @param array $headerAttribute
     * @return mixed
     * @throws XmlResponseException
     */
    function array2xml($array, $xml = false, $headerAttribute = [])
    {
        if (!$this->isType(gettype($array))){
            throw new XmlResponseException('It is not possible to convert the data');
        }

        if (!is_array($array)){
            $array = $array->toArray();
        }

        if($xml === false){
            $xml = new \SimpleXMLElement($this->app()->get('xml.template'));
        }

        $this->addAttribute($headerAttribute, $xml);

        foreach($array as $key => $value){
            if(is_array($value)){

                if (is_numeric($key)){
                    $this->array2xml($value, $xml->addChild($this->caseSensitive('row_' . $key)));
                } else {
                    $this->array2xml($value, $xml->addChild($this->caseSensitive($key)));
                }
            } else{
                $xml->addChild($key, htmlspecialchars($value));
            }
        }

        return Response::make($xml->asXML(), 200, $this->header());
    }
}