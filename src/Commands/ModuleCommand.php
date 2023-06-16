<?php

namespace PlugNPlay\Commands;

use Illuminate\Console\Command;

class ModuleCommand extends Command
{
    protected $signature = 'plug-n-play:module {name : The name of the module}
                                             {--enable : Enable the module}
                                             {--disable : Disable the module}';

    protected $description = 'Enable or disable a module for the admin panel';

    public function handle()
    {
        $moduleName = $this->argument('name');
        $enable = $this->option('enable');
        $disable = $this->option('disable');

        if ($enable && $disable) {
            $this->error('Please provide either --enable or --disable option, not both.');

            return;
        }

        if (! $enable && ! $disable) {
            $this->error('Please provide either --enable or --disable option.');

            return;
        }

        $moduleConfigPath = config_path('adminpanel/modules.php');
        $moduleConfig = require $moduleConfigPath;

        if (! isset($moduleConfig[$moduleName])) {
            $this->error('Module does not exist.');

            return;
        }

        if ($enable) {
            $moduleConfig[$moduleName] = true;
            $this->info('Module enabled successfully.');
        }

        if ($disable) {
            $moduleConfig[$moduleName] = false;
            $this->info('Module disabled successfully.');
        }

        file_put_contents($moduleConfigPath, '<?php'.PHP_EOL.'return '.var_export($moduleConfig, true).';');
    }
}
