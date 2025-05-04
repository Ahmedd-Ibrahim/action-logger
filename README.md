# BIM ActionLogger

BIM ActionLogger is a powerful Laravel package that extends Spatie's Activity Log package to provide route-based batch activity logging. It allows you to automatically log user actions during HTTP requests and process them with custom processors.

## Features

- **Automatic Batch Logging**: Logs all activities in a single HTTP request as a batch
- **Route-Based Processing**: Select custom activity processors based on route names or patterns
- **Controller-Based Processing**: Select processors based on controller action names
- **Clean Activity Display**: Format activity logs in a human-readable way
- **Middleware Integration**: Simple integration with Laravel's middleware system

## Installation

You can install the package via composer:

```bash
composer require bim/action-logger
```

After installing, publish the configuration file:

```bash
php artisan vendor:publish --provider="BIM\ActionLogger\ActionLoggerServiceProvider"
```

## Configuration

After publishing the configuration, you can find it at `config/action-logger.php`. The configuration allows you to:

- Define default processors for activities
- Configure route-based processors
- Configure controller-based processors
- Set batch handling options

### Example Configuration

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Default Processors
    |--------------------------------------------------------------------------
    |
    | This is the default processor class that will be used when no specific
    | processor is found for an activity.
    |
    */
    'default_processors' => [
        'default' => \BIM\ActionLogger\Processors\BatchActionProcessor::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Processors
    |--------------------------------------------------------------------------
    |
    | Here you may define processors for specific routes.
    | The key should be the route name or pattern, and the value should be
    | the fully qualified class name of the processor.
    |
    | Example:
    | 'users.create' => \App\Processors\UserCreateProcessor::class,
    | 'users.*' => \App\Processors\UserProcessor::class,
    |
    */
    'route_processors' => [
        'users.update' => \App\Processors\UserUpdateProcessor::class,
        'projects.*' => \App\Processors\ProjectActionProcessor::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Controller Processors
    |--------------------------------------------------------------------------
    |
    | Here you may define processors for specific controller actions.
    | The key should be the controller@action, and the value should be
    | the fully qualified class name of the processor.
    |
    | Example:
    | 'App\Http\Controllers\UserController@store' => \App\Processors\UserCreateProcessor::class,
    |
    */
    'controller_processors' => [
        'App\Http\Controllers\UserController@update' => \App\Processors\UserUpdateProcessor::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Batch Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure batch logging behavior.
    |
    */
    'batch' => [
        'delete_discarded' => false,
        'auto_end' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded Routes
    |--------------------------------------------------------------------------
    |
    | Routes that should be excluded from automatic batch logging.
    |
    */
    'excluded_routes' => [
        'horizon*',
        'telescope*',
        'debugbar*',
    ],
];
```

## Usage

### Middleware Setup

To enable automatic batch logging for HTTP requests, add the middleware to your HTTP kernel:

```php
// app/Http/Kernel.php

protected $middlewareGroups = [
    'web' => [
        // ... other middleware
        \BIM\ActionLogger\Middleware\AutoBatchLoggingMiddleware::class,
    ],
];
```

Or add it to specific routes:

```php
Route::middleware('auth', \BIM\ActionLogger\Middleware\AutoBatchLoggingMiddleware::class)
    ->group(function () {
        // Your routes here
    });
```

### Manual Batch Control

You can also manually control batches in your code:

```php
use BIM\ActionLogger\Facades\ActionLogger;

// Start a batch
ActionLogger::startBatch();

// Log activities inside the batch
activity()
    ->performedOn($user)
    ->log('updated');

activity()
    ->performedOn($profile)
    ->log('created');

// Commit the batch (saves all activities with the batch UUID)
ActionLogger::commitBatch();

// Or discard the batch if needed
ActionLogger::discardBatch();
```

### Creating Custom Processors

Create custom processors by extending the `BaseActionProcessor` class:

```php
namespace App\Processors;

use BIM\ActionLogger\Processors\BaseActionProcessor;
use Spatie\Activitylog\Models\Activity;

class UserActionProcessor extends BaseActionProcessor
{
    /**
     * Process the activities
     *
     * @return array
     */
    protected function processActivities(): array
    {
        // Get common data for all activities
        $user = $this->getCommonCauser();
        $action = $this->getCommonAction();
        
        // Get subject details
        $subject = $this->getCommonSubject();
        $subjectType = $subject ? class_basename($subject) : null;
        
        // Return processed data
        return [
            'type' => 'user_action',
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
            ] : null,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subject ? $subject->id : null,
            'description' => "User updated their profile",
            'changes' => $this->collectChanges(),
            'timestamp' => $this->getLatestTimestamp()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Format a batch message
     *
     * @param array $batchData Processed batch data
     * @return string Formatted message
     */
    public function formatBatchMessage(array $batchData): string
    {
        return "User {$batchData['user']['name']} {$batchData['action']} their profile.";
    }
}
```

### Example Response from ActionLogger

When you process activities using ActionLogger, you'll get structured data that can be used in your application. Here's an example of the response format:

```php
// Get activities for a user
$activities = ActionLogger::forSubject($user)->get();

// Process the activities
$result = ActionLogger::process($activities);
```

Example response from `process()`:

```php
[
    'type' => 'user_action',
    'user' => [
        'id' => 1,
        'name' => 'John Doe',
    ],
    'action' => 'updated',
    'subject_type' => 'User',
    'subject_id' => 1,
    'description' => 'User updated their profile',
    'changes' => [
        'name' => [
            'old' => 'John',
            'new' => 'John Doe',
        ],
        'email' => [
            'old' => 'john@example.com',
            'new' => 'johndoe@example.com',
        ],
    ],
    'timestamp' => '2023-05-15 14:30:45',
    'processor' => 'App\\Processors\\UserActionProcessor',
]
```

Example batch response:

```php
// Process a batch of activities
$batchUuid = '123e4567-e89b-12d3-a456-426614174000';
$result = ActionLogger::processBatch($activities, $batchUuid);
```

Example batch response:

```php
[
    'uuid' => '123e4567-e89b-12d3-a456-426614174000',
    'type' => 'user_profile_update',
    'user' => [
        'id' => 1,
        'name' => 'John Doe',
    ],
    'count' => 3,
    'actions' => ['updated', 'created'],
    'subjects' => [
        'User' => [1],
        'Profile' => [1],
    ],
    'description' => 'John Doe updated their profile and added contact information',
    'changes' => [
        'name' => [
            'old' => 'John',
            'new' => 'John Doe',
        ],
        'email' => [
            'old' => 'john@example.com',
            'new' => 'johndoe@example.com',
        ],
        'phone' => [
            'old' => null,
            'new' => '+1234567890',
        ],
    ],
    'timestamp' => '2023-05-15 14:30:45',
    'processor' => 'App\\Processors\\UserProfileUpdateProcessor',
]
```

### Formatting Messages

You can also get formatted messages for your activities:

```php
// Format a batch message
$message = ActionLogger::formatBatchMessage($activities);

// Example result: "John Doe updated their profile and added contact information."
```

## Real-World Example

Here's a controller example showing how ActionLogger works with a real application:

```php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Http\Request;
use BIM\ActionLogger\Facades\ActionLogger;

class UserController extends Controller
{
    public function update(Request $request, User $user)
    {
        // Start a batch
        ActionLogger::startBatch();
        
        try {
            // Update user data
            $user->update($request->only(['name', 'email']));
            
            // Update profile
            $user->profile->update($request->only(['phone', 'address']));
            
            // Commit the batch
            ActionLogger::commitBatch();
            
            return response()->json([
                'message' => 'User profile updated successfully',
                'user' => $user->fresh(['profile']),
            ]);
        } catch (\Exception $e) {
            // Discard the batch if there's an error
            ActionLogger::discardBatch();
            
            return response()->json([
                'message' => 'Failed to update user profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    public function activity(User $user)
    {
        // Get all activities for this user
        $activities = ActionLogger::forSubject($user)->get();
        
        // Process the activities
        $processedActivities = ActionLogger::process($activities);
        
        return response()->json([
            'activities' => $processedActivities,
        ]);
    }
}
```

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.