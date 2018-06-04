<?php

namespace Solvecrew\ExpoNotificationsBundle\Manager;

use GuzzleHttp\Exception\ClientException;
use Solvecrew\ExpoNotificationsBundle\Model\NotificationContentModel;

class NotificationManager
{
    // The info to hint invalid notification messages.
    const INVALID_MESSAGE_INFO = 'Invalid message provided.';
    // The info to hint invalid expo notifications API endpoint loaded using the configuration.
    const INVALID_EXPO_ENDPOINT_MESSAGE = 'Invalid Expo API endpoint configured.';
    // The info to hint a connection exception.
    const CONNECT_EXCEPTION_MESSAGE = 'Connection could not be established.';
    // The info that indicates that an unknown exception was thrown.
    const UNKNOWN_EXCEPTION_MESSAGE = 'A Exception was thrown. Neither a ConnectionException nor a ClientException.';
    // The info that the response was not given, but no exception was catched still.
    const HTTP_UNHANDLED_ERROR = 'The http response was not recieved. No Exception occured.';

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
     * @param array $data
     *
     * @return array
     */
    public function sendNotification(
        $message,
        $token,
        $title = '',
        $data = null
    )
    {
        $notificationContentModel = new NotificationContentModel();
        $notificationContentModel
            ->setTo($token)
            ->setBody($message);

        if (strlen($title) > 0) {
            $notificationContentModel->setTitle($title);
        }

        if (is_array($data)) {
            $notificationContentModel->setData($data);
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

        $notificationContentModel = $this->handleHttpResponse([$httpResponse], [$notificationContentModel]);

        return $notificationContentModel[0];
    }

    /**
     * Handle the overall process of multiple new notifications.
     *
     * @param array $messages
     * @param array $tokens
     * @param array $titles
     * @param array $data
     *
     * @return array
     */
    public function sendNotifications(
        $messages,
        $tokens,
        $titles = [],
        $data = []
    )
    {
        if (count($messages) !== count($tokens)) {
            return [];
        }

        $notificationContentModels = $this->createNotificationContentModels($tokens, $messages, $titles, $data);

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
    private function validateMessage($message)
    {
        if (strlen($message) === 0) {
            return false;
        }

        return true;
    }

    /**
     * Sends an HTTP request to the expo API to issue a push notification.
     *
     * @param NotificationContentModel $notificationContentModel
     *
     * @return array
     */
    private function sendNotificationHttp(NotificationContentModel $notificationContentModel)
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

        $exceptionResponse = [
            'status' => 'error',
            'message' => '',
            'details' => [self::HTTP_UNHANDLED_ERROR],
        ];

        try {
            $response = $this->httpClient->request(
                'POST',
                $this->expoApiUrl,
                $requestData
            );
        } catch (ClientException $e) {
            // Creating a Message from the status code and the reasonPhrase. E.g. '404: Not Found'.
            $exceptionMessage = $e->getResponse()->getStatusCode() . ': ' . $e->getResponse()->getReasonPhrase();

            // Returning an array in the style of the guzzle reponse, so it can be handled by the standard function.
            $exceptionResponse = [
                'status' => 'error',
                'message' => $exceptionMessage,
                'details' => [self::INVALID_EXPO_ENDPOINT_MESSAGE],
            ];
        } catch (ConnectException $e) {
            // Creating a Message from the status code and the reasonPhrase. E.g. '404: Not Found'.
            $exceptionMessage = 'No Response.';

            // Returning an array in the style of the guzzle reponse, so it can be handled by the standard function.
            $exceptionResponse = [
                'status' => 'error',
                'message' => $exceptionMessage,
                'details' => [self::CONNECT_EXCEPTION_MESSAGE],
            ];
        } catch (Exception $e) {
            // Creating a Message from the status code and the reasonPhrase. E.g. '404: Not Found'.
            $exceptionMessage = 'An unknown Exception occured.';

            // Returning an array in the style of the guzzle reponse, so it can be handled by the standard function.
            $exceptionResponse = [
                'status' => 'error',
                'message' => $exceptionMessage,
                'details' => [self::UNKNOWN_EXCEPTION_MESSAGE],
            ];
        }

        if(!$response) {
            return $exceptionResponse;
        }

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
    public function sendNotificationsHttp($notificationContentModels)
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

        try {
            $response = $this->httpClient->request(
                'POST',
                $this->expoApiUrl,
                $requestData
            );
        } catch (ClientException $e) {
            // Creating a Message from the status code and the reasonPhrase. E.g. '404: Not Found'.
            $exceptionMessage = $e->getResponse()->getStatusCode() . ': ' . $e->getResponse()->getReasonPhrase();

            // Returning an array in the style of the guzzle reponse, so it can be handled by the standard function.
            $exceptionResponse = [
                'status' => 'error',
                'message' => $exceptionMessage,
                'details' => [self::INVALID_EXPO_ENDPOINT_MESSAGE],
            ];
        } catch (ConnectException $e) {
            // Creating a Message from the status code and the reasonPhrase. E.g. '404: Not Found'.
            $exceptionMessage = 'No Response.';

            // Returning an array in the style of the guzzle reponse, so it can be handled by the standard function.
            $exceptionResponse = [
                'status' => 'error',
                'message' => $exceptionMessage,
                'details' => [self::CONNECT_EXCEPTION_MESSAGE],
            ];
        } catch (Exception $e) {
            // Creating a Message from the status code and the reasonPhrase. E.g. '404: Not Found'.
            $exceptionMessage = 'An unknown Exception occured.';

            // Returning an array in the style of the guzzle reponse, so it can be handled by the standard function.
            $exceptionResponse = [
                'status' => 'error',
                'message' => $exceptionMessage,
                'details' => [self::UNKNOWN_EXCEPTION_MESSAGE],
            ];
        }

        if(!$response) {
            $exceptionResponseArray = [];
            $i = 0;
            while ($i < count($notificationContentModels)) {
                $exceptionResponseArray[] = $exceptionResponse;
                $i++;
            }

            return $exceptionResponseArray;
        }

        $responseData = json_decode($response->getBody()->read(1024), true);

        return $responseData['data'];
    }

    /**
     * Maps the given tokens and messages to proper NotificationContentModels.
     *
     * @param array $tokens
     * @param array $messages
     * @param array $titles
     * @param array $data
     *
     * @return array
     */
    private function createNotificationContentModels(
        $tokens,
        $messages,
        $titles = [],
        $data = []
    )
    {
        $notificationContentModels = [];

        $hasTitle = false;
        if (count($titles) > 0) {
            $hasTitle = true;
        }

        $hasData = false;
        if (count($data) > 0) {
            $hasData = true;
        }

        foreach ($tokens as $key => $token) {
            $model = new NotificationContentModel();
            $model
                ->setTo($token)
                ->setBody($messages[$key]);

            if ($hasTitle && strlen($titles[$key]) > 0) {
                $model->setTitle($titles[$key]);
            }

            if ($hasData && is_array($data[$key]) > 0) {
                $model->setData($data[$key]);
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
    public function handleHttpResponse(
        $httpResponse,
        $notificationContentModels
    )
    {
        foreach ($httpResponse as $key => $httpResponseDetails) {
            // Being pessimistic here.
            $wasSuccessful = false;

            if ($httpResponseDetails['status'] != 'error') {
                $wasSuccessful = true;
            } else {
                // Set the response message if there is one.
                if (isset($httpResponseDetails['message'])
                    && strlen($httpResponseDetails['message']) > 0
                ) {
                    $notificationContentModels[$key]->setResponseMessage($httpResponseDetails['message']);
                }

                // Set the response detail if there is one.
                if (isset($httpResponseDetails['details'])
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
     * Returns JSON if wanted.
     *
     * @param array $notificationContentModels
     *
     * @return array
     */
    private function createRequestBody($notificationContentModels)
    {
        $requestData = [];

        foreach ($notificationContentModels as $model) {
            $requestData[] = $model->getRequestData();
        }

        return $requestData;
    }
}
