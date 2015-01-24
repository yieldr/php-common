<?php

namespace Yieldr\Http\Negotiation;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Negotiation\AcceptHeader;
use Negotiation\Negotiator;
use Negotiation\FormatNegotiator;
use Negotiation\LanguageNegotiator;

class NegotiationServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['negotiator.format'] = $app->share(function ($app) {
            return new FormatNegotiator();
        });

        if (!isset($app['negotiator.format.priorities'])) {
            $app['negotiator.format.priorities'] = [];
        }

        $app['negotiator.language'] = $app->share(function ($app) {
            return new LanguageNegotiator();
        });

        if (!isset($app['negotiator.language.priorities'])) {
            $app['negotiator.language.priorities'] = [];
        }

        $app['negotiator.encoding'] = $app->share(function ($app) {
            return new Negotiator();
        });

        $app['negotiator.encoding.modes'] = [
            'gzip'    => FORCE_GZIP,
            'deflate' => FORCE_DEFLATE,
        ];

        if (!isset($app['negotiator.encoding.priorities'])) {
            $app['negotiator.encoding.priorities'] = [];
        }

        $app['negotiator.format.default'] = new AcceptHeader('application/json', 0);
        $app['negotiator.language.default'] = new AcceptHeader('en-US', 0);
    }

    public function boot(Application $app)
    {
        $app->before(function (Request $request) use ($app) {
            $app['negotiator.format.best_match'] = $app['negotiator.format']->getBest(
                $request->headers->get('Accept'),
                $app['negotiator.format.priorities']);

            $app['negotiator.language.best_match'] = $app['negotiator.language']->getBest(
                $request->headers->get('Accept-Language'),
                $app['negotiator.language.priorities']);

            $app['negotiator.encoding.best_match'] = $app['negotiator.encoding']->getBest(
                $request->headers->get('Accept-Encoding'),
                $app['negotiator.encoding.priorities']);
        }, Application::EARLY_EVENT);
    }
}