<?php

namespace Solvecrew\ExpoNotificationsBundle\Manager;

use Solvecrew\ExpoNotificationsBundle\Model\NotificationContentModel;

class NotificationManager
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var GuzzleClient
     */
    private $httpClient;

    /**
     * @var string
     */
    private $expoApiUrl;

    public function __construct(
        $entityManager,
        $guzzleClient,
        $expoApiUrl
    ) {
        $this->entityManager = $entityManager;
        $this->httpClient = $guzzleClient;
        $this->expoApiUrl = $expoApiUrl;
    }

    /**
     * Handle the overall process of a new notification.
     *
     * @param string $notificationData
     * @param string $token
     *
     * @return array
     */
    public function sendNotification(
        string $message,
        string $token
    ): array
    {
        // Createn response array initially.
        $response = [
            'success' => false,
            'message' => null
        ];

        // Validate the request data.
        $isMessageValid = $this->validateMessage($message);

        if (!$isMessageValid) {
            $response['message'] = 'Invalid message provided.';

            return $response;
        }

        $httpResponse = $this->sendNotificationHttp($token, $message);

        // TODO Handle response and set message and success on response.

        if ($httpResponse['status'] != 'error') {
            $response['success'] = true;
        }
        $response['message'] = $httpResponse['message'];

        return $response;
    }

    /**
     * Validation of the message.
     *
     * @param string $message
     *
     * @return bool
     */
    private function validateMessage(string $message)
    {
        if (strlen($message) === 0) {
            return false;
        }

        return true;
    }

    /**
     * Sends an HTTP request to the expo API to issue a push notification.
     *
     * @param string $token
     * @param string $message
     *
     * @return array
     */
    private function sendNotificationHttp(string $token, string $message)
    {
        $content = new NotificationContentModel();
        $content
            ->setTo($token);
        $content
            ->setBody($message);

        $headers = [
            'accept' => 'application/json',
            'accept-encoding' => 'gzip, deflate',
            'content-type' => 'application/json',
        ];

        $requestData = [
            'headers' => $headers,
            'body' => $content->getJson(),
        ];

        $response = $this->httpClient->request(
            'POST',
            '',
            $requestData
        );

        // TODO Handle Response here.

        return json_decode($response->getBody()->read(1024), true)['data'][0];
    }
}
