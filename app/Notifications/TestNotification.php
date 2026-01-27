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
            // CHANGE 1: Use 'data' instead of 'setData'
            ->data([
                'type' => 'test_notification',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ])
            // CHANGE 2: Use 'notification' instead of 'setNotification' (if setNotification fails too)
            ->notification(
                FcmNotification::create()
                    ->title('Test Notification 🔔') // 'setTitle' -> 'title' (sometimes required in older versions)
                    ->body('If you can read this, your FCM setup is working perfectly!')
            )
            // CHANGE 3: The 'custom' method usually works, but if it fails, remove it for a simple test first.
            ->custom([
                'android' => [
                    'notification' => [
                        'color' => '#10B981',
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