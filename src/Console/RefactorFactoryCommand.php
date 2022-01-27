<?php
declare(strict_types=1);

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
    protected $description = 'Refactor the style of factory call for laravel 8.x.';

    private $testDir;

    private $modelDir;

    private $files;

    private $testNamespace = 'Tests\\';

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
        $this->testDir = $this->option('test_dir') ?? 'tests';
        $this->modelDir = $this->option('model_dir') ?? 'app/Models';

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
            ['test_dir', 'T', InputOption::VALUE_OPTIONAL, 'The test directory'],
            ['model_dir', 'M', InputOption::VALUE_OPTIONAL, 'The model directory'],
        ];
    }

    /**
     * @noinspection PhpUndefinedMethodInspection
     */
    protected function loadModels(): array
    {
        $rootDirectory = basename($this->laravel->path());

        if (!file_exists($this->laravel->basePath($this->modelDir))) {
            $this->error('Model directory does not exists.');

            return [];
        }

        return array_map(function (SplFIleInfo $file) use ($rootDirectory) {
            return str_replace(
                ['/', DIRECTORY_SEPARATOR, "$rootDirectory\\"],
                ['\\', '\\', $this->laravel->getNamespace()],
                $this->formatPath($file->getPath(), basename($file->getFilename(), '.php'))
            );
        }, $this->files->allFiles($this->modelDir));
    }

    protected function loadTests(): array
    {
        if (!file_exists($this->laravel->basePath($this->testDir))) {
            $this->error('Test directory does not exists.');

            return [];
        }

        return array_map(function (SplFIleInfo $file) {
            return str_replace(
                ['/', DIRECTORY_SEPARATOR, lcfirst($this->testNamespace)],
                ['\\', '\\', $this->testNamespace],
                $this->formatPath($file->getPath(), basename($file->getFilename(), '.php'))
            );
        }, $this->files->allFiles($this->testDir));
    }

    protected function formatPath(string ...$paths): string
    {
        return implode(DIRECTORY_SEPARATOR, $paths);
    }

    protected function replaceFactoryStyleToClassBased(string $content): string
    {
        foreach ($this->loadModels() as $model) {
            $content = str_replace($this->createSearchArr($model), $this->createReplaceArr($model),
                preg_replace($this->createPregSearchArr($model), $this->createPregReplaceArr($model), $content));
        }
        return $content;
    }

    protected function createSearchArr(string $model): array
    {
        return ['factory(' . $this->getModelBaseName($model) . '::class)', 'factory(' . $model . '::class)'];
    }

    protected function createReplaceArr(string $model): array
    {
        return [$this->getModelBaseName($model) . '::factory()', $model . '::factory()'];
    }

    protected function createPregSearchArr(string $model): array
    {
        return [
            '/factory\(\s*' . $this->getModelBaseName($model) . '::class\s*,\s*([0-9]*)\s*\)/',
            '/factory\(\s*' . str_replace('\\', '\\\\', $model) . '::class\s*,\s*([0-9]*)\s*\)/',
        ];
    }

    protected function createPregReplaceArr(string $model): array
    {
        return [
            $this->getModelBaseName($model) . "::factory()->count($1)",
            $model . "::factory()->count($1)",
        ];
    }

    protected function getModelBaseName(string $model): string
    {
        return basename($model);
    }
}
