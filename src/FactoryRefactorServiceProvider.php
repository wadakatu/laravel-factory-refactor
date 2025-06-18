<?php
declare(strict_types=1);

namespace Wadakatu\LaravelFactoryRefactor;

use Illuminate\Support\ServiceProvider;
use Wadakatu\LaravelFactoryRefactor\Console\RefactorFactoryCommand;

class FactoryRefactorServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole() === false) {
            return;
        }

        $this->commands([RefactorFactoryCommand::class]);
    }
}
