<?php

namespace Wadakatu\LaravelFactoryRefactor\Console;

use Exception;
use SplFileInfo;
use ReflectionClass;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;

class RefactorFactoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'refactor:factory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'refactor the style of factory.';

    private $dir;

    private $namespace = 'Tests\\';

    private $files;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->dir = $this->option('dir') ?? 'tests';

        $tests = $this->loadTests();
        $this->info(count($tests) . " Tests Found.");

        foreach ($tests as $test) {
            if (!Str::contains($test, ['test', 'Test', 'TEST'])) {
                $this->comment('Not a test file. Skipped. ' . $test);
                continue;
            }
            try {
                $reflection = new ReflectionClass($test);
                $fileName = $reflection->getFileName();
                $content = File::get($fileName);
                $replaceContent = $this->replaceFactoryStyleToClassBased($content);
                if (strcmp($content, $replaceContent) === 0) {
                    $this->comment("Nothing changed. " . basename($fileName));
                    continue;
                }
                File::put($fileName, $replaceContent);
                $this->info("Factory style changed. " . basename($fileName));
            } catch (Exception $e) {
                $this->comment("Could not analyze class $test.\nException: {$e->getMessage()}");
                continue;
            }
        }

        return 0;
    }

    protected function getOptions(): array
    {
        return [
            ['dir', 'D', InputOption::VALUE_OPTIONAL, 'The test directory'],
        ];
    }

    protected function loadTests(): array
    {
        if (!file_exists($this->laravel->basePath($this->dir))) {
            $this->error('Test directory does not exists.');

            return [];
        }

        return array_map(function (SplFIleInfo $file) {
            return str_replace(
                ['/', DIRECTORY_SEPARATOR, lcfirst($this->namespace)],
                ['\\', '\\', $this->namespace],
                $this->formatPath($file->getPath(), basename($file->getFilename(), '.php'))
            );
        }, $this->files->allFiles($this->dir));
    }

    protected function formatPath(string ...$paths): string
    {
        return implode(DIRECTORY_SEPARATOR, $paths);
    }

    protected function replaceFactoryStyleToClassBased(string $content): string
    {
        return preg_replace($this->createPregSearchArr(), $this->createPregReplaceArr(), $content);
    }

    protected function createPregSearchArr(): array
    {
        return [
            '/factory\(\s*([a-zA-Z,\\\\]+)::class\s*\)/',
            '/factory\(\s*([a-zA-Z,\\\\]+)::class\s*\,\s*([0-9]*)\s*\)/',
        ];
    }

    protected function createPregReplaceArr(): array
    {
        return [
            '$1' . "::factory()",
            '$1' . "::factory()->count($2)",
        ];
    }
}
