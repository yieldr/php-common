<?php

namespace Yieldr\Http\Negotiation;

use Symfony\Component\HttpFoundation\Response;

trait NegotiationTrait
{
    /**
     * Responds to a request.
     *
     * @param  array   $data    The response data
     * @param  integer $status  The response status code
     * @param  array   $headers An array of response headers
     *
     * @return Response
     */
    public function respond($data = array(), $status = 200, array $headers = array())
    {
        $response = new Response();

        $format = $this['negotiator.format.best_match'] ?: $this['negotiator.format.default'];
        $encoding = $this['negotiator.encoding.best_match'];

        $content = ''; // the content after we format it.

        try {
            $content = $this['serializer']->serialize(
                $data,
                $this['negotiator.format']->getFormat($format->getValue()));
        } catch (\Exception $ex) {
            $response->setStatusCode(Response::HTTP_NOT_ACCEPTABLE);
            $response->headers->set('X-Reason-Not-Acceptable', $ex->getMessage());
        }

        if ($encoding) {
            $encodingMode = $this['negotiator.encoding.modes'][$encoding->getValue()];
            $content = gzencode($content, -1, $encodingMode);
            $response->headers->set('Content-Encoding', $encoding->getValue());
        }

        $response->setContent($content);
        $response->headers->set('Content-Type', $format->getValue());

        return $response;
    }

    /**
     * Deserializes the incoming request.
     *
     * @return mixed
     */
    public function deserialize($class = '')
    {
        try {
            return $app['serializer']->deserialize(
                $this['request']->getContent(),
                $class,
                $this['request']->getContentType());
        } catch (\Exception $ex) {
            $this->abort(Response::HTTP_UNPROCESSABLE_ENTITY, $ex->getMessage());
        }
    }
}