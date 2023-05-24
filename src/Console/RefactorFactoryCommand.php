<?php

namespace Wadakatu\LaravelFactoryRefactor\Console;

use Exception;
use SplFileInfo;
use ReflectionClass;
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

    /**
     * target directory
     *
     * @var string
     */
    private $dir;

    /**
     * target namespace
     *
     * @var string
     */
    private $namespace;

    /** @var Filesystem $files */
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
    public function handle(): int
    {
        $this->dir = $this->option('dir') ?? 'tests';
        $this->namespace = $this->option('namespace');

        $files = $this->loadFiles();
        $this->info(count($files) . " Files Found.");

        foreach ($files as $file) {
            try {
                $reflection = new ReflectionClass($file);
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
                $this->comment("Could not analyze class $file.\nException: {$e->getMessage()}");
                continue;
            }
        }

        return 0;
    }

    protected function getOptions(): array
    {
        return [
            ['dir', 'D', InputOption::VALUE_OPTIONAL, 'Select a directory you want.'],
            ['namespace', 'N', InputOption::VALUE_OPTIONAL, 'Set specific namespace.'],
        ];
    }

    protected function loadFiles(): array
    {
        if (!file_exists($this->laravel->basePath($this->dir))) {
            $this->error('Selected directory does not exists.');

            return [];
        }

        return array_map(function (SplFileInfo $file) {
            $paths = $this->namespace
                ? [
                    $this->namespace,
                    $file->getRelativePath(),
                    basename($file->getFilename(), '.php'),
                ]
                : [
                    $file->getPath(),
                    basename($file->getFilename(), '.php'),
                ];
            return str_replace(
                ['/', DIRECTORY_SEPARATOR, 'tests\\'],
                ['\\', '\\', 'Tests\\'],
                $this->formatPath($paths),
            );
        }, $this->files->allFiles($this->dir));
    }

    protected function formatPath(array $paths): string
    {
        return implode(DIRECTORY_SEPARATOR, array_filter($paths));
    }

    protected function replaceFactoryStyleToClassBased(string $content): string
    {
        return preg_replace($this->createPregSearchArr(), $this->createPregReplaceArr(), $content);
    }

    protected function createPregSearchArr(): array
    {
        return [
            '/factory\(\s*([a-zA-Z,\\\\]+)::class\s*\)/',
            '/factory\(\s*([$\[\]\'a-zA-Z0-9,\\\\]+)::class\s*\,\s*([$a-zA-Z0-9\'\[\]]*)\s*\)/',
            '/factory\(\s*([$\[\]\'a-zA-Z,\\\\]+)\s*\)/',
            '/factory\(\s*([$\[\]\'a-zA-Z0-9]+)\s*\,\s*([$a-zA-Z0-9\'\[\]]*)\s*\)/',
        ];
    }

    protected function createPregReplaceArr(): array
    {
        return [
            '$1' . "::factory()",
            '$1' . "::factory()->count($2)",
            '$1' . "::factory()",
            '$1' . "::factory()->count($2)",
        ];
    }
}
