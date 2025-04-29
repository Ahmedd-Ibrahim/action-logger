# Action Logger

A Laravel package for managing action logging with support for CRUD operations, custom actions, and translations.

## Installation

You can install the package via composer:

```bash
composer require bim/action-logger
```

Publish the configuration file:

```bash
php artisan vendor:publish --provider="BIM\ActionLogger\ActionLoggerServiceProvider" --tag="config"
```

Publish the translation files:

```bash
php artisan vendor:publish --provider="BIM\ActionLogger\ActionLoggerServiceProvider" --tag="translations"
```

## Usage

### Using Facades

The package provides facades for easy logging:

```php
use BIM\ActionLogger\Facades\ActionLogger;
use BIM\ActionLogger\Facades\LogBatch;
use BIM\ActionLogger\Enums\Action;

// Log a single action
ActionLogger::on($model)
    ->by(auth()->user())
    ->withProperties(['extra_data' => 'value'])
    ->withLogName('custom_log')
    ->create();

// Log multiple actions in a batch
LogBatch::startBatch();

ActionLogger::on($model1)->update();
ActionLogger::on($model2)->update();
ActionLogger::on($model3)->update();

$batchUuid = LogBatch::getUuid();
LogBatch::endBatch();

// Later, retrieve all activities in the batch
$activities = Activity::forBatch($batchUuid)->get();
```

### Using Helper Functions

The package also provides helper functions:

```php
use BIM\ActionLogger\Enums\Action;

// Log a single action
log_action(
    $model,
    auth()->user(),
    Action::CREATED,
    ['extra_data' => 'value'],
    'custom_log'
);

// Log multiple actions in a batch
start_batch();

log_action($model1, auth()->user(), Action::UPDATED);
log_action($model2, auth()->user(), Action::UPDATED);
log_action($model3, auth()->user(), Action::UPDATED);

$batchUuid = get_batch_uuid();
end_batch();
```

### Getting Action Logs

You can retrieve logs with translated descriptions:

```php
// Get all logs with translated descriptions
$logs = get_action_logs();

// Get logs with custom query modifications
$logs = get_action_logs(function($query) {
    return $query
        ->where('log_name', 'custom_log')
        ->whereDate('created_at', '>=', now()->subDays(7))
        ->with(['subject', 'causer'])
        ->latest();
});

// Get logs for a specific batch
$logs = get_action_logs(function($query) use ($batchUuid) {
    return $query->forBatch($batchUuid);
});

// Get logs for a specific model
$logs = get_action_logs(function($query) use ($model) {
    return $query
        ->where('subject_type', get_class($model))
        ->where('subject_id', $model->id);
});
```

### Batch Logging

You can log multiple actions in a batch:

```php
use BIM\ActionLogger\Facades\LogBatch;
use BIM\ActionLogger\Enums\Action;
use Spatie\Activitylog\Models\Activity;

// Using facades
LogBatch::startBatch();

$author = Author::create(['name' => 'Philip K. Dick']);
$book = Book::create(['name' => 'A Scanner Brightly', 'author_id' => $author->id]);
$book->update(['name' => 'A Scanner Darkly']);
$author->delete();

LogBatch::endBatch();

// Using helpers
start_batch();
// ... log activities ...
$batchUuid = get_batch_uuid();
end_batch();

// Using callback
within_batch(function($uuid) {
    $item = NewsItem::create(['name' => 'new batch']);
    $item->update(['name' => 'updated']);
    $item->delete();
});
```

### Keeping Batch Open Across Jobs/Requests

You can keep a batch open across multiple jobs or requests:

```php
use BIM\ActionLogger\Facades\LogBatch;
use Illuminate\Bus\Batch;
use Illuminate\Support\Str;

$uuid = Str::uuid();

Bus::batch([
    new SomeJob('some value', $uuid),
    new AnotherJob($uuid),
    new WorkJob('work work work', $uuid),
])->then(function (Batch $batch) {
    // All jobs completed successfully...
})->catch(function (Batch $batch, Throwable $e) {
    // First batch job failure detected...
})->finally(function (Batch $batch) use ($uuid) {
    // The batch has finished executing...
    LogBatch::endBatch();
})->dispatch();

class SomeJob
{
    public function handle(string $value, ?string $batchUuid = null)
    {
        LogBatch::startBatch();
        if($batchUuid) {
            LogBatch::setBatch($batchUuid);
        }
        // other code ..
    }
}
```

### Basic Usage

```php
use BIM\ActionLogger\Services\ActionLoggerService;
use BIM\ActionLogger\Enums\Action;

class YourController extends Controller
{
    protected $actionLogger;

    public function __construct(ActionLoggerService $actionLogger)
    {
        $this->actionLogger = $actionLogger;
    }

    public function store(Request $request)
    {
        $model = Model::create($request->all());
        
        $this->actionLogger
            ->on($model)
            ->by(auth()->user())
            ->create(['extra_data' => 'value']);
    }
}
```

### Custom Actions with Macros

You can add custom action methods using macros:

```php
// In a service provider's boot method
use BIM\ActionLogger\Services\ActionLoggerService;
use App\Enums\CustomAction;

ActionLoggerService::macro('approve', function (array $extraProperties = []) {
    return $this->log(CustomAction::APPROVED, $extraProperties);
});

ActionLoggerService::macro('reject', function (array $extraProperties = []) {
    return $this->log(CustomAction::REJECTED, $extraProperties);
});
```

Then use the custom actions:

```php
// Using facade
ActionLogger::on($model)
    ->by(auth()->user())
    ->approve(['reason' => 'Approved by manager']);

// Using service instance
$this->actionLogger
    ->on($model)
    ->by(auth()->user())
    ->reject(['reason' => 'Missing documentation']);
```

### Custom Action Class

You can create your own action enum by implementing the `ActionInterface`:

```php
namespace App\Enums;

use BIM\ActionLogger\Contracts\ActionInterface;

enum CustomAction: string implements ActionInterface
{
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case CUSTOM = 'custom';

    public function getTranslationKey(): string
    {
        return 'action-logger::messages.' . $this->value;
    }

    public function getModelTranslationKey(string $model): string
    {
        return 'action-logger::models.' . strtolower($model);
    }

    public function value(): string
    {
        return $this->value;
    }
}
```

Then update the configuration to use your custom action class:

```php
// config/action-logger.php
'action_class' => \App\Enums\CustomAction::class,
```

### Custom Description

```php
ActionLogger::on($model)
    ->by(auth()->user())
    ->withDescription('Custom action description')
    ->log(Action::CREATED);
```

### Custom Log Name

```php
ActionLogger::on($model)
    ->by(auth()->user())
    ->withLogName('custom_log')
    ->update();
```

### Model Translations

Add model translations in the configuration file:

```php
// config/action-logger.php
'model_translations' => [
    'App\Models\User' => 'user',
    'App\Models\Post' => 'post',
],
```

And add the translations in your language files:

```php
// resources/lang/vendor/action-logger/en/models.php
return [
    'user' => 'User',
    'post' => 'Post',
];
```

## Customizing Translations

You can customize the translation messages by editing the files in `resources/lang/vendor/action-logger/`.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.