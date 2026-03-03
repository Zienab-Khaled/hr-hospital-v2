<?php

namespace App\Providers;

use App\Console\Commands\StorageLinkCommand;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // استبدال أمر storage:link بأمر آمن عندما exec/symlink معطّلين (مثل Hostinger)
        Artisan::starting(function () {
            Artisan::forgetCommand('storage:link');
            Artisan::registerCommand(new StorageLinkCommand);
        });

        Passport::tokensExpireIn(now()->addHours(1));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));

        // Use Tailwind pagination views
        Paginator::useTailwind();

        Gate::before(function ($user, $ability) {
            if (!$user) {
                return null;
            }
            if (method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo($ability)) {
                return true;
            }
            // تفويض: إذا المُستخدم مفوّض إليه من مدير/أدمن لفترة تشمل اليوم، يعامل كأنه يملك صلاحية المُفوّض
            if (method_exists($user, 'hasPermissionViaDelegation') && $user->hasPermissionViaDelegation($ability)) {
                return true;
            }
            return null;
        });

        Blade::directive('currency', function ($expression) {
            return "<?php echo \App\Helpers\CurrencyHelper::format($expression); ?>";
        });
    }
}
