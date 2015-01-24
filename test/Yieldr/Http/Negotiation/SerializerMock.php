<?php

namespace Yieldr\Http\Negotiation;

class SerializerMock
{
	public function serialize($data, $format)
	{
        switch ($format) {
            case 'xml':
                $xml = new \SimpleXMLElement('<response/>');
                foreach ($data as $key => $value) {
                    $xml->addChild($key, $value);
                }
                return $xml->asXML();

            case 'json':
                return json_encode($data);

            default:
                throw new \Exception('Unknown format ' . $format);
        }
	}
}