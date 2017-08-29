README
======

This Bundles purpose is to handle the BE tasks of the push notifications service for the expo react-native Framework.

# Installation

Install the bundle using composer (Working after official release):
```
composer require solvecrew/ExpoNotificationsBundle
```

Enable the Bundle in the app/AppKernel.php file:
```
$bundles = [
    ...
    new Solvecrew\ExpoNotificationsBundle\SCExpoNotificationsBundle(),
    ...
];
```
# Configuration

At the moment, this bundle only has a single optional configuration parameter.

If you want (optional), add this to your `app/config/config.yml` file:
```
sc_expo_notifications:
    expo_api_endpoint: '%expo_api_endpoint%'
```

And then add the `expo_api_endpoint` parameter in your `app/config/parameters.yml` file:
```
expo_api_endpoint: https://exp.host/--/api/v2/push/send
```

If you prefer to not add it as a parameter in your `parameters.yml` file you can add the URI in your config.yml file directly:
```
sc_expo_notifications:
    expo_api_endpoint: https://exp.host/--/api/v2/push/send
```

__IMPORTANT__: All this is completely OPTIONAL. If you don't add the config at all, it will use `https://exp.host/--/api/v2/push/send` as fallback since it is the endpoint from the official Expo Documentation.

# Usage

This bundle provides you with an easy way to send push notifications for a front-end application using the Expo
React-Native framework. Therefore the bundle provides you with several helpful things:
- NotificationContentModel: A model representing the requestdata for a single notification. As specified by the Expo
API.
- NotificationManager: A Manager to handle the preparing of the notification, the sending and the reponse.

The service of the NotificationManager is `sc_expo_notifications.notification_manager`.
- Use it in a controller with `$this->container->get('sc_expo_notifications.notification_manager')`.
- Inject it as a dependency like:
```
app.example_manager:
    class: AcmeBundle\Manager\ExampleManager
    arguments: ['@sc_expo_notifications.notification_manager']
```
NOTE that the important part here is the `arguments: ['@sc_expo_notifications.notification_manager']` of course.

After you have the NotificationManager available you can access its functions.
Popular functions are:

1. sendNotifications(...): Send multiple notifications in one API request.

```
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
        array $messages,
        array $tokens,
        array $titles = [],
        array $data = []
    ): array
    {
		...
    }
```

Therefore you need to provide an array of `messages` as strings and an array of `tokens` as strings (to be more
specific: The recipients ExponentPushToken. Like `sITGtlHf1-mSgUyQIVbVMJ`, without the `ExponentPushToken[]`
sourrounding.). The first message in the messages array will be delivered to the first token (recipient) in the tokens
array. And so on. Optionally you can provide a `titles` array which holds titles for the notifications. Last, you can
provide an array of data arrays that will be added to the notification as a JSON object for further handling in the
front-end. It is important to know, that each notification needs an array as data! See the Full Example below for more
information.

The function returns you an array of NotificationContentModel. One for each notification that was tried to send.
Those NotificationContentModels hold all the information about the notification.

For example:
- to: The token that represents the recipient.
- title: The title, if provided.
- body: The actual message of the notification.
- wasSuccessful: A boolean indicating whether the notification was send (does NOT mean it was recieved or viewed).
- responseMessage: A message that was returned by the Expo API on unsuccessful request for the specific notification.
- responseDetails: An array holding error specific information.

2. sendNotification(...): Send a single notification providing only a message string and a token. Optionally a title.

```
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
        string $message,
        string $token,
        string $title = '',
        array $data = null
    ): NotificationContentModel
    {
		...
    }
```
As you can see, this one is really straight forward. It returns a single NotificationContentModel as descibed above.
The title (string) and the data (array) are optional. If provided, $data must be an array.

## Full Example

To even ease the integration process further, see the following example.

```
// Get an instance of the NotificationManager provided by this bundle.
// Using the service, that is available since the bundle installation.
// Better would be to inject the service as a dependency in your service configuration.
$notificationManager = $this->get('sc_expo_notifications.notification_manager');

// Prepare the titles as you wish. If none would be provided, the app name will be a fallback by Expo.
$titles = [
    'New Notification',
    'Hot news',
];

// Prepare the messages that shall be sent. This will be more sophisticated under realistic circumstances...
$messages = [
    'Hello there!',
    'What's up?!',
];

// Prepare the ExpoPushTokens of the recipients.
$tokens = [
    'H-Dsb2ATt2FHoD_5rVG5rh',
    'S_Fs-1ATt4AHDD_5rXcYr4',
];

// Prepare the data that you want to pass to the front-end to help you handle the notification.
$data = [
	['foo' => 'bar', 'baz' => 'boom'],
	['whatever' => 'you', 'want' => 'here'],
];

// Send the notifications using the messages and the tokens that will receive them.
$notificationContentModels = $notificationManager->sendNotifications(
    $messages,
    $tokens,
    $titles,
    $data
);

// Handle the response here. Each NotificationContentModel in the $notificationContentModels array
// holds the information about its success/error and more detailed information.
```

If your use case is more complex or you just want to leverage more of the notification funtions you can use the
`sendNotificationHttp` function of the NotificationManager. For that you need to create the NotificationContentModel
yourself.
```
// Use statement for the NotificationContentModel.
use Solvecrew\ExpoNotificationsBundle\Model\NotificationContentModel;

// Get an instance of the NotificationManager provided by this bundle.
// Using the service, that is available since the bundle installation.
// Better would be to inject the service as a dependency in your service configuration.
$notificationManager = $this->get('sc_expo_notifications.notification_manager');

$token = 'H-Dsb2ATt2FHoD_5rVG5rh';
$message = 'The message of the notification.';
$data = ['foo' => 'bar'];

$notificationContentModel = new NotificationContentModel();
$notificationContentModel
    ->setTo($token)
    ->setBody($message)
    ->setData($data)
	->setPriority('medium');

// Send the notification.
$httpResponse = $notificationManager->sendNotificationHttp($notificationContentModel);

// Handle the response using the notificationManager. Enriches the NotifcationContentModel with the http response data.
$notificationContentModel = $notificationManager->handleHttpResponse($httpResponse, [$notificationContentModel]);

```

If you want to send multiple notifications this way, use `sendNotificationsHttp` (plural).
```
// Use statement for the NotificationContentModel.
use Solvecrew\ExpoNotificationsBundle\Model\NotificationContentModel;

// Get an instance of the NotificationManager provided by this bundle.
// Using the service, that is available since the bundle installation.
// Better would be to inject the service as a dependency in your service configuration.
$notificationManager = $this->get('sc_expo_notifications.notification_manager');

$data = ['foo' => 'bar'];

// Create a NotificationContentModel
$notificationContentModel = new NotificationContentModel();
$notificationContentModel
    ->setTo('H-Dsb2ATt2FHoD_5rVG5rh')
    ->setBody('test message')
    ->setData($data)
	->setPriority('low');

// Create a second NotificationContentModel
$anotherNotificationContentModel = new NotificationContentModel();
$anotherNotificationContentModel
    ->setTo('Z-5sb2AFt2FHoD_5rVG5rh')
    ->setBody('Your message here')
    ->setData($data)
	->setPriority('medium');

$notificationContentModels = [
    $notificationContentModel,
    $anotherNotificationContentModel,
];

// Send the notifications.
$httpResponse = $notificationManager->sendNotificationsHttp($notificationContentModels);

// Handle the response using the notificationManager. Enriches the NotifcationContentModel with the http response data.
$notificationContentModels = $notificationManager->handleHttpResponse($httpResponse, $notificationContentModels);

// The notificationContentModels have now been updated. The info for each notification is now stored in each model.
```
# Troubleshooting

If the service `sc_expo_notifications.notification_manager` is not available for some reason, debug your container with
`bin/console debug:container | grep notification`.
You should see:
```
sc_expo_notifications.guzzle_client                GuzzleHttp\Client
sc_expo_notifications.notification_manager         Solvecrew\ExpoNotificationsBundle\Manager\NotificationManager
```

The first service is the guzzle client which is the dependency of our bundle.
The second service is the notificationManager the bundle provides to handle all notification related tasks.


# Based on the Expo push notifications API

To see the process and the API documentation for the Expo push notifications service see:
https://docs.expo.io/versions/v14.0.0/guides/push-notifications.html

# LICENSE
MIT
