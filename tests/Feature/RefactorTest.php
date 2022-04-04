<?php

declare(strict_types=1);

namespace Wadakatu\LaravelFactoryRefactor\Tests\Feature;

use Illuminate\Filesystem\Filesystem;
use Orchestra\Testbench\TestCase as TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Wadakatu\LaravelFactoryRefactor\Console\RefactorFactoryCommand;

class RefactorTest extends TestCase
{
    const REFACTOR_RESULT = [
        'User::factory()->make();',
        'App\Models\User::factory()->make();',
        'User::factory()->count(10)->make();',
        'App\Models\User::factory()->count(10)->make();',
        '$model::factory()->make();',
        'User::factory()->count($count[\'user\'])->make();',
        '$model::factory()->count(10)->make();',
        '$model::factory()->count($count)->make();',
        '$model[\'user\']::factory()->count($count[0])->make();',
        '$model[\'user\'][0]::factory()->count($count[0][1])->make();',
        '$model[0]::factory()->count($count[\'user\'])->make();',
    ];

    private $commandTester;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->setBasePath('./');
        $file = new Filesystem();
        $command = new RefactorFactoryCommand($file);
        $command->setLaravel($this->app);
        $this->commandTester = new CommandTester($command);
    }

    /**
     * @test
     */
    public function handle()
    {
        $resultBefore = $this->extractFactoryCallBeforeRefactoring('tests/Files/RefactorTarget.php');
        $this->assertSame(11, count($resultBefore));

        $this->commandTester->execute(
            [
                '--dir' => 'tests/Files',
                '--namespace' => 'Wadakatu\LaravelFactoryRefactor\Tests\Files'
            ]
        );

        $result = $this->extractFactoryCallAfterRefactoring('tests/Files/RefactorTarget.php');

        copy('tests/RefactorTarget_bak.php', 'tests/Files/RefactorTarget.php');

        $this->assertSame(count(self::REFACTOR_RESULT), count($result));
        $this->assertSame(self::REFACTOR_RESULT, $result);
    }

    private function extractFactoryCallBeforeRefactoring(string $filePath)
    {
        $beforeRefactor = file_get_contents($filePath);
        preg_match_all('/factory\([\s\S]+?\)/', $beforeRefactor, $matchBefore);

        return $matchBefore[0];
    }

    private function extractFactoryCallAfterRefactoring(string $filePath): array
    {
        $afterRefactor = file_get_contents($filePath);
        preg_match_all('/(?<={)[\s\S]+?(?=})/', $afterRefactor, $resultAfter);

        $resultAfter = array_map(
            function ($value) {
                return trim($value);
            },
            explode("\n", $resultAfter[0][0])
        );

        $deleteKeys = [0, 1, 2, 3, 4, 5, 17];
        foreach ($deleteKeys as $key) {
            unset($resultAfter[$key]);
        }

        return array_values($resultAfter);
    }
}
