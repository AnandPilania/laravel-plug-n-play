<?php

namespace PlugNPlay\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use PlugNPlay\Actions\GetComposer;

class PlugNPlayCommand extends Command
{
    protected $signature = 'plug-n-play {name : The name of the plugin}
                                        {--make : Create the plugin}
                                        {--enable : Enable the plugin}
                                        {--disable : Disable the plugin}
                                        {--force : Replace the existing}';

    protected $description = 'Create or Enable/Disable a plugin.';

    protected GetComposer $composer;

    public function __construct()
    {
        parent::__construct();

        $this->composer = new GetComposer();
    }

    public function handle(): int
    {
        $pluginName = $this->argument('name');

        if ($hasMake = $this->option('make')) {
            $force = $this->option('force');

            $name = $pluginName = str($pluginName)->studly()->ucfirst();

            $this->generate($name, $force);
        }

        $enable = $this->option('enable');
        $disable = $this->option('disable');

        if ($enable && $disable) {
            $this->error('Please provide either --enable or --disable option, not both.');

            return 0;
        }

        if (!$hasMake && !$enable && !$disable) {
            $this->error('Please provide either --enable or --disable option.');

            return 0;
        }

        if($hasMake && !$enable && !$disable) {
            return 0;
        }

        $pluginPath = __DIR__ . '/../../' . $this->composer->get('extra.plugins-dir') . '/' . $pluginName;
        $pluginConfigPath = $pluginPath . '/config.php';

        if (!file_exists($pluginConfigPath)) {
            $this->error('Plugin ' . $pluginName . ' OR config file not exists!');

            return 0;
        }

        $pluginConfig = require $pluginConfigPath;

        if ($enable) {
            $pluginConfig['enabled'] = true;
        }

        if ($disable) {
            $pluginConfig['enabled'] = false;
        }

        file_put_contents($pluginConfigPath, '<?php' . PHP_EOL . 'return ' . var_export($pluginConfig, true) . ';');

        $this->info('Plugin ' . ($enable ? 'enabled' : 'disabled') . ' successfully.');

        return 1;
    }

    public function generate($pluginName, $force)
    {
        $this->info('Generating plugin: ' . $pluginName . "\n");

        $config = config('plug-n-play.creator');

        $namespace = $config['namespace'];
        $str = str($pluginName);
        $pluginNamePlural = $str->plural();
        $pluginNameLower = $str->lower();
        $pluginNameLowerPlural = str($pluginNameLower)->plural();

        $composerVendor = $config['composer']['vendor'];
        $composerAuthor = $config['composer']['author']['name'];
        $composerAuthorEmail = $config['composer']['author']['email'];

        $search = ['{{pluginName}}', '{{pluginNamePlural}}', '{{pluginNameLower}}', '{{pluginNameLowerPlural}}', '{{namespace}}', '{{composerVendor}}', '{{composerAuthor}}', '{{composerAuthorEmail}}'];
        $replace = [$pluginName, $pluginNamePlural, $pluginNameLower, $pluginNameLowerPlural, $namespace, $composerVendor, $composerAuthor, $composerAuthorEmail];

        $basePath = __DIR__ . '/../../' . $this->composer->get('extra.plugins-dir') . '/' . $pluginName;

        if (File::isDirectory($basePath)) {
            if ($force) {
                $this->warn("Plugin already exists. Replacing...\n");
                File::deleteDirectory($basePath);
                File::makeDirectory($basePath);
                $this->info("- '$basePath' - directory created");
                $this->createFiles($pluginName, $basePath, $search, $replace, $force);
            } else {
                $this->error(" Plugin '$pluginName' already exists. Use --force to replace. ");
            }
        } else {
            File::makeDirectory($basePath);
            $this->info("- '$basePath' - directory created");

            $this->createFiles($pluginName, $basePath, $search, $replace, $force);
        }
    }

    public function createFiles($pluginName, $basePath, $search, $replace, $force)
    {
        $str = str($pluginName);
        $pluginNamePlural = $str->plural();
        $pluginNameLower = $str->lower();
        $pluginNameLowerPlural = str($pluginNameLower)->plural();

        $config = config('plug-n-play.creator');

        $stubs_path = $config['stubs']['path'];

        $files_list = $config['plugin']['files'];

        foreach ($files_list as $file => $file_path) {
            $content_stub = File::get("$stubs_path/" . $file_path[0]);
            $content = str_replace($search, $replace, $content_stub);

            $destination_value = $this->setFilePath($file, $file_path[1], $pluginName);

            $destination = "$basePath/" . $this->setFilePath($file, $file_path[1], $pluginName);

            $pathToFile = $destination_value;

            if (count(explode('/', $pathToFile)) > 1) {
                $fileName = basename($pathToFile);

                $folders = explode('/', str_replace('/' . $fileName, '', $pathToFile));

                $currentFolder = "$basePath/";
                foreach ($folders as $folder) {
                    $currentFolder .= $folder . DIRECTORY_SEPARATOR;

                    if (!File::isDirectory($currentFolder)) {
                        File::makeDirectory($currentFolder);
                    }
                }
            }

            if (File::exists($destination)) {
                if ($force) {
                    File::put($destination, $content);
                    $this->info("- '$destination' - file replaced");
                } else {
                    $this->error("- '$destination' - file already exists");
                }
            } else {
                File::put($destination, $content);
                $this->info("- '$destination' - file created");
            }
        }
    }

    public function setFilePath($filetype, $filePath, $pluginName)
    {
        $value = '';
        $str = str($pluginName);
        $pluginNamePlural = $str->plural();
        $pluginNameLower = $str->lower();
        $pluginNameLowerPlural = str($pluginNameLower)->plural();

        switch ($filetype) {
            case 'command':
                $value = $pluginName . 'Command.php';
                $filePath = str_replace('StubCommand.php', $value, $filePath);
                break;

            case 'database':
                $value = date('Y_m_d_his_') . 'create_' . $pluginNameLowerPlural . '_table.php';
                $filePath = str_replace('stubMigration.php', $value, $filePath);
                break;

            case 'factories':
                $value = $pluginName . 'Factory.php';
                $filePath = str_replace('stubFactory.php', $value, $filePath);
                break;

            case 'seeders':
                $value = $pluginName . 'DatabaseSeeder.php';
                $filePath = str_replace('stubSeeders.php', $value, $filePath);
                break;

            case 'models':
                $value = $pluginName . '.php';
                $filePath = str_replace('stubModel.php', $value, $filePath);
                break;

            case 'providers':
                $value = $pluginName . 'ServiceProvider.php';
                $filePath = str_replace('stubServiceProvider.php', $value, $filePath);
                break;

            case 'controller_backend':
                $value = $pluginNamePlural . 'Controller.php';
                $filePath = str_replace('stubBackendController.php', $value, $filePath);
                break;

            case 'controller_frontend':
                $value = $pluginNamePlural . 'Controller.php';
                $filePath = str_replace('stubFrontendController.php', $value, $filePath);
                break;

            case 'views_backend_index':
                $value = $pluginNameLowerPlural;
                $filePath = str_replace('stubViews', $value, $filePath);
                break;

            case 'views_backend_index_datatable':
                $value = $pluginNameLowerPlural;
                $filePath = str_replace('stubViews', $value, $filePath);
                break;

            case 'views_backend_create':
                $value = $pluginNameLowerPlural;
                $filePath = str_replace('stubViews', $value, $filePath);
                break;

            case 'views_backend_form':
                $value = $pluginNameLowerPlural;
                $filePath = str_replace('stubViews', $value, $filePath);
                break;

            case 'views_backend_show':
                $value = $pluginNameLowerPlural;
                $filePath = str_replace('stubViews', $value, $filePath);
                break;

            case 'views_backend_edit':
                $value = $pluginNameLowerPlural;
                $filePath = str_replace('stubViews', $value, $filePath);
                break;

            case 'views_backend_trash':
                $value = $pluginNameLowerPlural;
                $filePath = str_replace('stubViews', $value, $filePath);
                break;

            case 'views_frontend_index':
                $value = $pluginNameLowerPlural;
                $filePath = str_replace('stubViews', $value, $filePath);
                break;

            case 'views_frontend_show':
                $value = $pluginNameLowerPlural;
                $filePath = str_replace('stubViews', $value, $filePath);
                break;

            default:
                // code...
                break;
        }

        return $filePath;
    }
}
