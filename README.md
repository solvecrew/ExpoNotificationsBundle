README
======

This Bundles purpose is to handle the BE tasks of the push notifications service for the expo react-native Framework.

# Installation

Install the bundle using composer (Working after official release):
```composer require solvecrew/ExpoNotificationsBundle```

Enable the Bundle in the app/AppKernel.php file:
```
	$bundles = [
		...
		new Solvecrew\ExpoNotificationsBundle\SCExpoNotificationsBundle(),
		...
	];
```
Add the following parameter in your app/config/parameters.yml file:
```
    expo_notification_url: https://exp.host/--/api/v2/push/send
```
NOTE: `https://exp.host/--/api/v2/push/send` is the default url. If you want to use another endpoint, change it as you
like.

# Configuration

At the moment, this bundle only has a single opional configuration parameter.

If you want (opional), add this to your `app/config/config.yml` file:
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

IMPORTANT: All this is completely OPTIONAL. If you don't add the config at all, it will use `https://exp.host/--/api/v2/push/send` as fallback since it is the endpoint from the official Expo Documentation.

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
     *
     * @return array
     */
    public function sendNotifications(
        array $messages,
        array $tokens,
        array $titles = []
    ): array
    {
		...
	}
```

Therefore you need to provide an array of `messages` as strings and an array of `tokens` as strings (to be more
specific: The recipients ExponentPushToken. Like `sITGtlHf1-mSgUyQIVbVMJ`, without the `ExponentPushToken[]`
sourrounding.). The first message in the messages array will be delivered to the first token (recipient) in the tokens
array. And so on. Optionally you can provide a `titles` array which holds titles for the notifications.

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
     *
     * @return array
     */
    public function sendNotification(
        string $message,
        string $token,
        string $title = ''
    ): NotificationContentModel
    {
		...
	}
```
As you can see, this one is really straight forward. It returns a single NotificationContentModel as descibed above.

## Full Example

To even ease the integration process further, see the following example.

```
// Get an instance of the NotificationManager provided by this bundle.
// Using the service, that is available since the bundle installation.
// Better would be to inject the service as a dependency in your service configuration.
$notificationManager = $this->get('sc_expo_notifications.notification_manager');

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

// Send the notifications using the messages and the tokens that will receive them.
$notificationContentModels = $notificationManager->sendNotifications(
    $notificationMessages,
    $notificationTokens
);

// Handle the response here. Each NotificationContentModel in the $notificationContentModels array holds the information about its success/error and more detailed information.
```

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
