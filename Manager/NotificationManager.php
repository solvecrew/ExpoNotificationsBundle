<?php

namespace Solvecrew\ExpoNotificationsBundle\Manager;

use Solvecrew\ExpoNotificationsBundle\Model\NotificationContentModel;

class NotificationManager
{
    // The info to hint invalid notification messages.
    const INVALID_MESSAGE_INFO = 'Invalid message provided.';

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
     * @param string $message
     * @param string $token
     * @param string $title
     *
     * @return array
     */
    public function sendNotification(
        string $message,
        string $token,
        string $title = ''
    ): NotificationContentModel
    {
        $notificationContentModel = new NotificationContentModel();
        $notificationContentModel
            ->setTo($token)
            ->setBody($message);

        if (strlen($title) > 0) {
            $notificationContentModel->setTitle($title);
        }

        // Validate the given message.
        $isMessageValid = $this->validateMessage($message);

        if (!$isMessageValid) {
            $notificationContentModel
                ->setWasSuccessful(false)
                ->setResponseMessage(self::INVALID_MESSAGE_INFO);

            return $notificationContentModel;
        }

        $httpResponse = $this->sendNotificationHttp($notificationContentModel);

        $notificationContentModel = handleHttpResponse($httpResponse, [$notificationContentModel]);

        return $notificationContentModel;
    }

    /**
     * Handle the overall process of multiple new notifications.
     *
     * @param array $messages
     * @param array $tokens
     * @param array $titles
     *
     * @return array
     */
    public function sendNotifications(
        array $messages,
        array $tokens,
        array $titles = []
    ): array
    {
        if (count($messages) !== count($tokens)) {
            return [];
        }

        $notificationContentModels = $this->createNotificationContentModels($tokens, $messages, $titles);

        $httpResponse = $this->sendNotificationsHttp($notificationContentModels);

        $notificationContentModels = $this->handleHttpResponse($httpResponse, $notificationContentModels);

        return $notificationContentModels;
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
     * @param array $notifications
     *
     * @return array
     */
    private function sendNotificationHttp(NotificationContentModel $notificationContentModel): array
    {
        $headers = [
            'accept' => 'application/json',
            'accept-encoding' => 'gzip, deflate',
            'content-type' => 'application/json',
        ];

        $requestData = [
            'headers' => $headers,
            'body' => json_encode([$notificationContentModel->getRequestData()]),
        ];

        $response = $this->httpClient->request(
            'POST',
            $this->expoApiUrl,
            $requestData
        );

        // TODO Handle Response here.

        $responseData = json_decode($response->getBody()->read(1024), true);
        return $responseData['data'][0];
    }

    /**
     * Sends an HTTP request to the expo API to issue multiple push notifications.
     *
     * @param array $notificationContentModels
     *
     * @return array
     */
    private function sendNotificationsHttp(array $notificationContentModels): array
    {
        $headers = [
            'accept' => 'application/json',
            'accept-encoding' => 'gzip, deflate',
            'content-type' => 'application/json',
        ];

        $requestData = [
            'headers' => $headers,
            'body' => json_encode(
                $this->createRequestBody($notificationContentModels)
            ),
        ];

        $response = $this->httpClient->request(
            'POST',
            $this->expoApiUrl,
            $requestData
        );

        $responseData = json_decode($response->getBody()->read(1024), true);

        return $responseData['data'];
    }

    /**
     * Maps the given tokens and messages to proper NotificationContentModels.
     *
     * @param array $tokens
     * @param array $messages
     * @param array $titles
     *
     * @return array
     */
    private function createNotificationContentModels(
        array $tokens,
        array $messages,
        array $titles = []
    ): array
    {
        $notificationContentModels = [];

        $hasTitle = false;
        if (count($titles) > 0) {
            $hasTitle = true;
        }

        foreach ($tokens as $key => $token) {
            $model = new NotificationContentModel();
            $model
                ->setTo($token)
                ->setBody($messages[$key]);
            if ($hasTitle && strlen($titles[$key]) > 0) {
                $model->setTitle($titles[$key]);
            }
            $notificationContentModels[] = $model;
        }

        return $notificationContentModels;
    }

    /**
     * Creates a detailed response array for the given notifications.
     *
     * param array $httpResponse
     * param array $notificationContentModels
     *
     * @return array
     */
    private function handleHttpResponse(
        array $httpResponse,
        array $notificationContentModels
    ): array
    {
        foreach ($httpResponse as $key => $httpResponseDetails) {
            // Being pessimistic here.
            $wasSuccessful = false;

            if ($httpResponseDetails['status'] != 'error') {
                $wasSuccessful = true;
            } else {
                // Set the response message if there is one.
                if ($httpResponseDetails['message']
                    && strlen($httpResponseDetails['message']) > 0
                ) {
                    $notificationContentModels[$key]->setResponseMessage($httpResponseDetails['message']);
                }

                // Set the response detail if there is one.
                if ($httpResponseDetails['details']
                    && count($httpResponseDetails['details']) > 0
                ) {
                    $notificationContentModels[$key]->setResponseDetails($httpResponseDetails['details']);
                }
            }

            $notificationContentModels[$key]->setWasSuccessful($wasSuccessful);
        }

        return $notificationContentModels;
    }

    /**
     * Creates an array of requestData arrays from given NotificationContentModels.
     *
     * @param array $notificationContentModels
     *
     * @return array
     */
    private function createRequestBody(array $notificationContentModels): array
    {
        $requestData = [];

        foreach ($notificationContentModels as $model) {
            $requestData[] = $model->getRequestData();
        }

        return $requestData;
    }
}
