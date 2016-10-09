<?php
namespace Larastarscn\Workbench;

use Illuminate\Filesystem\Filesystem;

class PackageCreator
{
    protected $package;
    protected $file;
    protected $needDirectories;
    protected $packagePath;

    public function __construct(Filesystem $file)
    {
        $this->file = $file;
    }

    public function setDirectories($directories)
    {
        if (!is_array($directories)) {
            if (empty($directories)) {
                return false;
            }

            $directories = explode(',', $directories);
        }

        $this->needDirectories = $directories;
    }


    public function create(Package $package, $directories)
    {
        $this->setDirectories($directories);
        $this->package = $package;

        $path = "packages/{$package->lowerVendor}/{$package->lowerName}";
        $fullPath = base_path($path);
        $this->packagePath = $fullPath;

        if (! $this->file->isDirectory($fullPath)) {
            $this->file->makeDirectory($fullPath, 0777, true);
        }

        $this->createDrictories();

        $this->writeComposer();

        $this->addNamespaceToComposer();

        return true;
    }

    public function addNamespaceToComposer()
    {
        $contents = $this->file->get(base_path('composer.json'));
        $composerArray = json_decode($contents, true);
        $searchPath = 'autoload.psr-4.'.$this->package->vendor. '\\' . $this->package->name . '\\';
        if (!array_has($composerArray, $searchPath)) {
            array_set($composerArray, $searchPath, 'packages/'. $this->package->lowerVendor. '/' . $this->package->lowerName. '/src/');
            $composerJson = json_encode($composerArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $this->file->put(base_path('composer.json'), $composerJson);
        }
    }

    public function writeComposer()
    {
        $contents = $this->file->get(__DIR__.'/stubs/composer.json.stub');
        foreach (get_object_vars($this->package) as $key => $value) {
            $contents = str_replace('{{'. snake_case($key) . '}}', $value, $contents);
        }
        $this->file->put($this->packagePath. '/composer.json', $contents);
    }

    public function createDrictories()
    {
        if (!$this->needDirectories) {
            return false;
        }

        foreach ($this->needDirectories as $value) {
            $value = studly_case($value);
            if (! $this->file->isDirectory($this->packagePath. '/src/' . $value)) {
                $this->file->makeDirectory($this->packagePath. '/src/' . $value, 0777, true);
            }
        }
    }
}
