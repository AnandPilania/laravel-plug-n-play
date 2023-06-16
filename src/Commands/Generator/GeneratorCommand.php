<?php

namespace PlugNPlay\Commands\Generator;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class GeneratorCommand
{
    /** @var string|null */
    public $outputPath;

    /** @var string|null */
    public $rootNamespace = 'PlugNPlay';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type;

    /** @var OutputInterface */
    protected $output;

    /** @var InputInterface */
    protected $input;

    /** @var Composer */
    protected $composer;

    public function __construct()
    {
        $this->composer = new Composer();
    }

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function __invoke(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $pluginName = $this->getPluginInput();

        if (! file_exists($this->getOutputPath().'/'.$pluginName)) {
            $this->error($pluginName.' Plugin not exists!');

            return false;
        }

        $plugin = $this->qualifyClass($pluginName);
        $name = $this->qualifyName($this->getNameInput());
        $path = $this->getPath($plugin, $name);

        if (! $this->input->getOption('force') && file_exists($path)) {
            $this->error($this->type.' already exists!');

            return false;
        }

        $this->makeDirectory(Str::before($path, basename($path)));

        file_put_contents($path, $this->buildClass($plugin, $name));

        $this->info($this->type.' created successfully.');
    }

    protected function qualifyName(string $name): string
    {
        return ltrim($name, '\\/');
    }

    protected function qualifyClass(string $plugin): string
    {
        $plugin = ltrim($plugin, '\\/');

        $rootNamespace = $this->rootNamespace();

        if (Str::startsWith($plugin, $rootNamespace)) {
            return Str::replaceFirst("{$rootNamespace}\{$this->type}", "{$rootNamespace}\{$plugin}\{$this->type}", $plugin);
        }

        return $this->qualifyClass(
            $this->getDefaultNamespace(trim($rootNamespace, '\\'), $plugin)
        );
    }

    protected function rootNamespace()
    {
        $autoload = $this->composer->get('autoload.psr-4');

        return array_keys($autoload)[1];
    }

    protected function getDefaultNamespace(string $rootNamespace, $name)
    {
        return $rootNamespace.'\\'.$name;
    }

    protected function getNameInput(): string
    {
        return trim($this->input->getArgument('name'));
    }

    protected function getPluginInput(): string
    {
        return trim($this->input->getArgument('plugin'));
    }

    protected function getPath($plugin, $name)
    {
        $plugin = str($plugin)->replace($this->rootNamespace(), '');
        $file = $name.$this->type;

        return $this->getOutputPath().str_replace('\\', '/', $plugin).'/'.$file.'.php';
    }

    protected function getOutputPath(): string
    {
        return $this->outputPath ?? (__DIR__.'/../../../'.($this->composer->get('extra.plugins-dir')) ?? getcwd().'/src').'/';
    }

    /**
     * Determine if the class already exists.
     *
     * @param  string  $rawName
     * @return bool
     */
    protected function alreadyExists($rawPluginName, $rawName)
    {
        return file_exists($this->getPath($rawPluginName, $this->qualifyName($rawName)));
    }

    public function error($text)
    {
        $this->output->write('<error>'.$text.'</error>');
    }

    protected function makeDirectory(string $path)
    {
        if (! mkdir($path, 0777, true) && ! is_dir($path)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
        }
    }

    protected function buildClass($plugin, $name)
    {
        $stub = file_get_contents($this->getStub());

        return $this->replaceNamespace($stub, $plugin)->replaceClass($stub, $name.$this->type);
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    abstract protected function getStub();

    protected function replaceClass($stub, $name)
    {
        $class = str_replace($this->getNamespace($name).'\\', '', $name);

        return str_replace('DummyClass', $class, $stub);
    }

    protected function getNamespace($name)
    {
        return trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
    }

    protected function replaceNamespace(&$stub, $name)
    {
        $stub = str_replace(
            ['DummyNamespace', 'DummyRootNamespace'],
            [$name, $this->rootNamespace()],
            $stub
        );

        return $this;
    }

    public function info($text)
    {
        $this->output->write('<info>'.$text.'</info>');
    }

    public function option($name, $default = null)
    {
        if ($this->input->hasOption($name)) {
            return $this->input->getOption($name) ?? $default;
        }

        return $default;
    }
}
