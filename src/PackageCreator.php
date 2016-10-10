<?php
namespace Larastarscn\Workbench;

use Illuminate\Filesystem\Filesystem;

class PackageCreator
{
    /**
     * The package instance.
     *
     * @var \Larastarscn\Workbench\Package
     */
    protected $package;

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $file;

    /**
     * The need to be created directories of the package.
     *
     * @var array
     */
    protected $needDirectories;

    /**
     * The full path of the package.
     *
     * @var string
     */
    protected $packagePath;

    /**
     * Create a new package creator instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem
     * @return void
     */
    public function __construct(Filesystem $file)
    {
        $this->file = $file;
    }

    /**
     * Set the need to be created directories of the package.
     *
     * @param  $directories  string|array
     * @return void
     */
    public function setDirectories($directories)
    {
        if (! is_array($directories)) {
            if (empty($directories)) {
                return false;
            }

            $directories = explode(',', $directories);
        }

        $this->needDirectories = $directories;
    }

    /**
     * Create a new package stub.
     *
     * @param  \Larastarscn\Workbench\Package  $package
     * @param  string  $directories
     * @return boolean
     */
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

    /**
     * Add the namespace of the package to composer.json of the project.
     *
     * @return void
     */
    public function addNamespaceToComposer()
    {
        $contents = $this->file->get(base_path('composer.json'));
        $composerArray = json_decode($contents, true);
        $searchPath = 'autoload.psr-4.'.$this->package->vendor. '\\' . $this->package->name . '\\';
        if (! array_has($composerArray, $searchPath)) {
            array_set($composerArray, $searchPath, 'packages/'. $this->package->lowerVendor. '/' . $this->package->lowerName. '/src/');
            $composerJson = json_encode($composerArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $this->file->put(base_path('composer.json'), $composerJson);
        }
    }

    /**
     * Write the composer.json stub to the package root.
     *
     * @return void
     */
    public function writeComposer()
    {
        $contents = $this->file->get(__DIR__.'/stubs/composer.json.stub');
        foreach (get_object_vars($this->package) as $key => $value) {
            $contents = str_replace('{{'. snake_case($key) . '}}', $value, $contents);
        }
        $this->file->put($this->packagePath. '/composer.json', $contents);
    }

    /**
     * Create the needed directories of the package.
     *
     * @return void
     */
    public function createDrictories()
    {
        if (! $this->needDirectories) {
            return false;
        }

        foreach ($this->needDirectories as $value) {
            $value = $this->fromatDirectoryPath($value);

            if (! $this->file->isDirectory($this->packagePath. '/src/' . $value)) {
                $this->file->makeDirectory($this->packagePath. '/src/' . $value, 0777, true);
            }
        }
    }

    /**
     * Format the directory path, support using 'dot' notation indicate the path.
     *
     * @param  string  $path
     * @return string
     */
    public function fromatDirectoryPath($path)
    {
            $path = trim($path, '.');
            $paths = explode('.', $path);
            $paths = array_map(function ($name) {
                return studly_case($name);
            }, $paths);
            return implode('/', $paths);
    }
}
