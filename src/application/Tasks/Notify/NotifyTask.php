<?php

namespace Tasks\Notify;

use Exception;
use League\OAuth2\Client\Provider\GenericProvider;
use Monolog\Logger;
use Phalcon\Cli\Task;
use Pheanstalk\Job;
use Pheanstalk\Pheanstalk;

class NotifyTask extends Task
{
    /**
     * @var Job
     */
    protected $job;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param Logger $logger
     *
     * @return NotifyTask
     */
    public function setLogger(Logger $logger): NotifyTask
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @param array $params
     * @param Job   $job
     *
     * @return bool
     */
    public function sendMobilePushAction(array $params, Job $job)
    {
        $this->job = $job;
        try {
            $status = $this->isSent($params['user_id']);
            if ($status['mobile'] === false) {
                $this->logger->debug('Sending mobile push to ' . $params['email'] . ' 
                for these cities: ' . json_encode($params['city']));
                $this->markSent($params['user_id'], 'mobile');
            } else {
                $this->logger->debug('Already sent mobile push to ' . $params['email']);
            }
            return true;
        } catch (Exception $e) {
            $this->logger->debug('Task failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * @param array $params
     * @param Job   $job
     *
     * @return bool
     */
    public function sendMailAction(array $params, Job $job)
    {
        $this->job = $job;
        try {
            $status = $this->isSent($params['user_id']);
            if ($status['email'] === false) {
                $this->logger->debug('Sending email to ' . $params['email'] . ' 
                for these cities: ' . json_encode($params['city']));
                $this->markSent($params['user_id'], 'email');
            } else {
                $this->logger->debug('Already sent email to ' . $params['email']);
            }
            return true;
        } catch (Exception $e) {
            $this->logger->debug('Task failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * @param array $params
     * @param Job   $job
     *
     * @return bool
     */
    public function subscriberAction(array $params, Job $job)
    {
        $this->job = $job;
        $this->logger->debug('Task in progress');
        try {
            /**
             * @var GenericProvider $client
             */
            $client = $this->di->get('oauth_client');
            $token  = $client->getAccessToken('client_credentials');

            /**
             * @var Pheanstalk $queue
             */
            $queue = $this->getDI()->get('queue');

            $collection   = $this->getUsers($client, $token, $params);
            $limit        = 2;
            $current_page = 1;
            $pages        = $collection['pagination']['page_count'];

            while ($current_page <= $pages) {
                $users = $this->getUsers($client, $token, $params, ($current_page - 1) * $limit, $limit)['items'];

                foreach ($users as $user) {
                    $data = json_encode(
                        [
                            'job'     => 'notify:sendMobilePushAction',
                            'payload' => $user,
                        ]
                    );

                    $queue->useTube('notification')->put($data);
                    $this->logger->debug('Daily mobile push task registered for ' . $user['email']);

                    $data = json_encode(
                        [
                            'job'     => 'notify:sendMailAction',
                            'payload' => $user,
                        ]
                    );
                    $queue->useTube('email')->put($data);
                    $this->logger->debug('Daily email push task registered for ' . $user['email']);
                }

                $current_page++;
            }

            return true;
        } catch (Exception $e) {
            $this->logger->debug('Task failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * @param GenericProvider $client
     * @param                 $token
     * @param array           $params
     * @param int             $offset
     * @param int             $limit
     *
     * @return array
     * @throws Exception
     */
    protected function getUsers(GenericProvider $client, $token, array $params, int $offset = 0, int $limit = 2): array
    {
        $options['field']  = 'user_id,email,onesignal_id,city';
        $options['limit']  = $limit;
        $options['offset'] = $offset;
        foreach ($params as $key => $param) {
            $options['filter']['timezone'][] = $param;
        }
        $options['filter']['status'] = 'active';

        $request = $client->getAuthenticatedRequest(
            'GET',
            $this->di->get('config')->get('oauth_client')['url'] . '/user' . '?' . http_build_query($options),
            $token
        );

        $response = $client->getParsedResponse($request);

        if ($response['code'] == 400) {
            throw new Exception($response['detail']);
        }

        return $response;
    }

    protected function isSent($user_id)
    {
        /**
         * @var GenericProvider $client
         */
        $client = $this->di->get('oauth_client');
        $token  = $client->getAccessToken('client_credentials');

        $request = $client->getAuthenticatedRequest(
            'GET',
            $this->di->get('config')->get('oauth_client')['url'] . '/user/' . $user_id . '/daily_notify_status',
            $token
        );

        $response = $client->getParsedResponse($request);
        return $response;
    }

    /**
     * @param        $user_id
     * @param string $type
     *
     * @return mixed
     * @throws Exception
     */
    protected function markSent($user_id, string $type)
    {
        $this->logger->debug('User ' . $type . ' notification marking at service');

        /**
         * @var GenericProvider $client
         */
        $client = $this->di->get('oauth_client');
        $token  = $client->getAccessToken('client_credentials');
        if ($type == 'email') {
            $options['body']                    = json_encode([
                'email_at' => date_create('now')->format('Y-m-d H:i:s'),
            ]);
            $options['headers']['Content-Type'] = 'application/json;charset=UTF-8';
        } elseif ($type == 'mobile') {
            $options['body']                    = json_encode([
                'mobile_push_at' => date_create('now')->format('Y-m-d H:i:s'),
            ]);
            $options['headers']['Content-Type'] = 'application/json;charset=UTF-8';
        } else {
            throw new Exception('notification type is not valid');
        }

        $request = $client->getAuthenticatedRequest(
            'PUT',
            $this->di->get('config')->get('oauth_client')['url'] . '/user/' . $user_id,
            $token,
            $options
        );

        $response = $client->getParsedResponse($request);
        return $response;
    }
}
