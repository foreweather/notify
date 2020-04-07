<?php
//  Add application configuration
return [
    'queue'        => [
        'host' => getenv('QUEUE_HOST'),
    ],
    'oauth_client' => [
        'client_id'              => getenv('OAUTH_CLIENT_ID'),    // The client ID assigned to you by the provider
        'client_secret'          => getenv('OAUTH_CLIENT_SECRET'),   // The client password assigned to you by the
        'access_token_url'       => getenv('OAUTH_TOKEN_URL'),
        'scope'                  => [],
        'url'                    => getenv('API_BASE_URL'),
        'redirect_uri'           => getenv('OAUTH_CLIENT_REDIRECT_URL'),
        'authorize_url'          => getenv('OAUTH_CLIENT_AUTHORIZE_URL'),
        'resource_owner_details' => getenv('OAUTH_OWNER_DETAILS'),
    ],
    'notify'       => [
        'hour'     => getenv('NOTIFY_HOUR'),
    ],
];
