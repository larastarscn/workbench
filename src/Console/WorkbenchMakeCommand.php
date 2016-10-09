<?php

namespace Larastarscn\Workbench\Console;

use Illuminate\Console\Command;
use Larastarscn\Workbench\Package;
use Larastarscn\Workbench\PackageCreator;

class WorkbenchMakeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workbench {package}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new package workbench';

    /**
     * The package creator instance.
     *
     * @var \Larastarscn\Workbench\PackageCreator
     */
    protected $creator;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(PackageCreator $creator)
    {
        parent::__construct();

        $this->creator = $creator;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $directories = $this->ask('What directories do you want?', null);

        $fails = ['null', 'no', 'n', 'false'];
        if (is_string($directories) && array_search($directories, $fails)) {
            $directories = '';
        }

        $package = $this->argument('package');
        $package = explode('/', $package);
        if (count($package) != 2) {
            $this->error('The package argument need to format like vendor/package.');
        }

        $vendor = studly_case($package[0]);
        $name = studly_case($package[1]);

        $packageInstance = new Package($vendor, $name, config('workbench.author'), config('workbench.email'));

        $this->info("Building {$vendor}/{$name}");
        $res = $this->creator->create($packageInstance, $directories);

        if ($res) {
            exec('composer dump-autoload');
            $this->info('');
            $this->info('Successed.');
        } else {
            $this->error('Something wrong.');
        }

    }
}
