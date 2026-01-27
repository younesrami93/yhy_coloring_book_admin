<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class TestNotification extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        return FcmMessage::create()
            ->setData([
                'type' => 'test_notification',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ])
            ->setNotification(
                FcmNotification::create()
                    ->setTitle('Test Notification 🔔')
                    ->setBody('If you can read this, your FCM setup is working perfectly!')
            )
            ->custom([
                'android' => [
                    'notification' => [
                        'color' => '#10B981', // Green color for success
                    ],
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                        ],
                    ],
                ],
            ]);
    }
}