<?php


namespace Providers;


use League\OAuth2\Client\Provider\GenericProvider;
use Phalcon\Config;
use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class OAuthClientProvider implements ServiceProviderInterface

{
    /**
     * Registers a service provider.
     *
     * @param DiInterface $di
     *
     * @return void
     */

    public function register(DiInterface $di): void
    {
        $config = $di->getShared('config')->get('oauth_client')->toArray();

        $di->setShared(
            'oauth_client',
            function () use ($config) {

                $provider = new GenericProvider([
                    'clientId'                => $config['client_id'],
                    'clientSecret'            => $config['client_secret'],
                    'redirectUri'             => $config['redirect_uri'],
                    'urlAuthorize'            => $config['authorize_url'],
                    'urlAccessToken'          => $config['access_token_url'],
                    'urlResourceOwnerDetails' => $config['resource_owner_details'],
                    'scope'                   => $config['scope'],
                ]);

                return $provider;

            }
        );
    }
}

