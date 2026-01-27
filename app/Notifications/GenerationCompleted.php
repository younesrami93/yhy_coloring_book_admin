<?php

namespace App\Notifications;

use App\Models\Generation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification; // Only import this one

class GenerationCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    protected $generation;

    public function __construct(Generation $generation)
    {
        $this->generation = $generation;
    }

    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        // 1. Prepare Data
        $imageUrl = $this->generation->processed_thumb_md ?? $this->generation->processed_image_url;
        $promptPreview = \Illuminate\Support\Str::limit($this->generation->prompt_used, 50);

        // 2. Create Message using generic arrays (Avoids "Class Not Found" errors)
        return FcmMessage::create()
            ->setData([
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'type' => 'generation_completed',
                'generation_id' => (string) $this->generation->id,
                'status' => 'completed',
            ])
            ->setNotification(
                FcmNotification::create()
                    ->setTitle('Your Art is Ready! 🎨')
                    ->setBody("Finished: \"$promptPreview\"")
                    ->setImage($imageUrl)
            )
            // 3. Use 'custom' to pass Android/iOS specific config as raw arrays
            ->custom([
                'android' => [
                    'notification' => [
                        'color' => '#4A90E2',
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'icon' => 'ic_notification', // Ensure this exists in your Android drawable folder
                    ],
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                            'mutable-content' => 1, // Allows images on iOS
                        ],
                        'generation_id' => (string) $this->generation->id, // Custom data for iOS
                    ],
                    'fcm_options' => [
                        'image' => $imageUrl, // iOS Image support
                    ],
                ],
            ]);
    }
}