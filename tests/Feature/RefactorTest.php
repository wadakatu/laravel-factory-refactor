<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Illuminate\Filesystem\Filesystem;
use Wadakatu\LaravelFactoryRefactor\Console\RefactorFactoryCommand;

class RefactorTest extends TestCase
{
    private $command;

    protected function setUp(): void
    {
        parent::setUp();
        $file = new Filesystem();
        $this->command = new RefactorFactoryCommand($file);
    }

    /**
     * @test
     */
    public function handle()
    {
        $beforeRefactor = file_get_contents('tests/Feature/Refactor.php');
        preg_match_all('/factory\(\s*([$\[\]\'a-zA-Z0-9]+)\s*,\s*([$a-zA-Z0-9\'\[\]]*)\s*\)/', $beforeRefactor, $match);
        var_dump($match);
        $this->command->handle();
    }
}
