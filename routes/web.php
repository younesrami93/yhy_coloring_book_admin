<?php

use App\Http\Controllers\Admin\AppUserController;
use App\Http\Controllers\Admin\GenerationController;
use App\Http\Controllers\Admin\StyleController;
use App\Http\Controllers\Api\RevenueCatWebhookController;
use App\Models\AppUser;
use App\Notifications\TestNotification;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;

Route::prefix('admin')->group(function () {
    // Guest Routes
    Route::middleware('guest')->group(function () {
        Route::get('login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AdminAuthController::class, 'authenticate']);
    });

    // Protected Routes
    Route::middleware('auth')->group(function () {
        Route::get('dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');
        Route::resource('styles', StyleController::class);
        Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/generations', [GenerationController::class, 'index'])->name('generations.index');
        Route::get('/users', [AppUserController::class, 'index'])->name('users.index');
        Route::delete('/users/{id}', [AppUserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{id}/credits', [AppUserController::class, 'addCredits'])->name('users.add_credits');
    });
});

Route::get('/debug-r2-raw', function () {
    try {
        $client = new S3Client([
            'region' => 'auto',
            'version' => 'latest',
            'endpoint' => env('R2_ENDPOINT'),
            'credentials' => [
                'key' => env('R2_ACCESS_KEY_ID'),
                'secret' => env('R2_SECRET_ACCESS_KEY'),
            ],
            'use_path_style_endpoint' => true,
        ]);
        
        $client->putObject([
            'Bucket' => env('R2_BUCKET'),
            'Key' => 'raw_debug.txt',
            'Body' => 'This is a test from the raw client.',
        ]);
        
        return "✅ Success! The issue is in Laravel Config, not the Connection.";
    } catch (\Exception $e) {
        return "❌ REAL ERROR: " . $e->getMessage();
    }
});

Route::get('/test-r2', function () {
    try {
        // Attempt to upload a simple text file
        Storage::disk('r2')->put('test_connection.txt', 'Hello Cloudflare R2!');
        return '✅ SUCCESS! File uploaded. URL: ' . Storage::disk('r2')->url('test_connection.txt');
    } catch (\Exception $e) {
        // Show the FULL technical error
        return '❌ ERROR: ' . $e->getMessage();
    }
});


Route::post('/webhooks/revenuecat', [RevenueCatWebhookController::class, 'handle']);


Route::get('/update-app', function () {
    chdir(base_path());

    $commands = [
        // 1. Discard manual changes on the server (Force clean)
        'git reset --hard HEAD 2>&1',

        // 2. Pull the latest code
        'git pull origin main 2>&1',

        // 3. Maintenance commands
        'composer install --no-dev --optimize-autoloader 2>&1',
        'php artisan migrate --force 2>&1',
        'php artisan config:clear 2>&1',
        'php artisan route:clear 2>&1',
        'php artisan view:clear 2>&1',
    ];

    $output = '';
    foreach ($commands as $command) {
        $output .= "$ $command\n";
        $output .= shell_exec($command) . "\n";
    }

    return response("<pre>$output</pre>");
});



Route::get('/test-notification', function () {
    // 1. Find all users who have at least one device connected
    $users = AppUser::has('devices')->get();

    if ($users->isEmpty()) {
        return response()->json([
            'status' => 'error', 
            'message' => 'No users with devices found in the database.'
        ]);
    }

    // 2. Send the notification to each of them
    foreach ($users as $user) {
        try {
            $user->notify(new TestNotification());
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send: ' . $e->getMessage()
            ]);
        }
    }

    return response()->json([
        'status' => 'success', 
        'message' => "Notification sent to {$users->count()} users!"
    ]);
});