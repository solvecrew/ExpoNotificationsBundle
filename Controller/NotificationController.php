<?php

namespace Solvecrew\ExpoNotificationsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class NotificationController extends Controller
{
    public function indexAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();

        // DEV TODO Create better user retrieval. Using Bundle:Entity and Entity field as config param.
        $user = $entityManager->getRepository('AppBundle:User')->findOneByAccessToken('asdf');

        // TODO Better validation.
        if (!$user || !$user->getExpoToken()) {
            return new JsonResponse(false);
        }

        // Get the manager.
        $notificationManager = $this->getNotificationManager();

        $message = 'Testing the notifications';

        $result = $notificationManager->sendNotification($message, $user->getExpoToken());

        return new JsonResponse($result);
    }

    /**
     * Function that returns the NotificationManager.
     *
     * @return NotificationManager
     */
    private function getNotificationManager()
    {
        return $this->get('sc_expo_notifications.notification_manager');
    }
}
