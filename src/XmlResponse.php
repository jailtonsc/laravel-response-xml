<?php

namespace XmlResponse;

use Illuminate\Contracts\Support\Arrayable;
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
     * @var
     */
    private $caseSensitive;

    /**
     * @var
     */
    private $template;

    private $showEmptyField;

    /**
     * XmlResponse constructor.
     */
    public function __construct()
    {
        $app = $this->app();

        $this->caseSensitive = $app->get('xml.caseSensitive');
        $this->template = $app->get('xml.template');
        $this->showEmptyField = $app->get('xml.showEmptyField');
    }

    /**
     * @return mixed
     */
    public function app()
    {
        $container = new Container();
        return $container->getInstance()->make('config');
    }

    /**
     * @return mixed
     */
    private function header()
    {
        return [
            'Content-Type' => 'application/xml'
        ];
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
        if ($this->caseSensitive){
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
    public function array2xml($array, $xml = false, $headerAttribute = [])
    {

        if (is_object($array) && $array instanceof Arrayable) {
            $array = $array->toArray();
        }

        if (!$this->isType(gettype($array))){
            throw new XmlResponseException('It is not possible to convert the data');
        }

        if($xml === false){
            $xml = new \SimpleXMLElement($this->template);
        }

        $this->addAttribute($headerAttribute, $xml);

        foreach($array as $key => $value){

            if(is_array($value)) {
                if (is_numeric($key)) {
                    $this->array2xml($value, $xml->addChild($this->caseSensitive('row_' . $key)));
                } else {
                    $this->array2xml($value, $xml->addChild($this->caseSensitive($key)));
                }
            }elseif (is_object($value)) {
                $this->array2xml($value, $xml->addChild($this->caseSensitive((new \ReflectionClass(get_class($value)))->getShortName())));
            } else{
                if (!is_null($value) || $this->showEmptyField) {
                    $xml->addChild($this->caseSensitive($key), htmlspecialchars($value));
                }
            }
        }

        return Response::make($xml->asXML(), 200, $this->header());
    }
}