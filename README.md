
# Larastarscn Workbench

[![License](https://poser.pugx.org/larastarscn/workbench/license.svg)](https://packagist.org/packages/larastarscn/workbench)

## Introduction

This package provides a simple convenient workbench for Laravel package creator.That will make you quickly create the package structure via command line interface.

## Installion

To get started with Workbench, add to your `composer.json` file as a dependency:

    composer require larastarscn/workbench

Then type the `composer install` command to the cli.

## Configure

After installing the Workbench libary, register the `Larastarscn\Workbench\WorkbenchServiceProvider` in your `config/app.php` configuration file:

    'providers' => [
        // Other service providers...

        Larastarscn\Workbench\WorkbenchServiceProvider::class,
    ]

Also, add the `WorkbenchMakeCommand` command class to the `commands` array in your `app/Console/Kernel.php` file:

    protected $commands = [
        Larastarscn\Workbench\Console\WorkbenchMakeCommand::class,
    ];

Then, you will need to publish the `workbench.php` configuration file to the `config` directory:

    php artisan vendor:publish

Also, you will need register the author infomation within `config/workbench.php`.

## Usage

Next, you are ready to create a new package via Workbench! Simple type fllowing command to the cli:

    php artisan workbench vendor/package

Just it! For example, if you want make a package that the name is `larastarscn/test`. Just run command like this:

    php artisan workbench larastarscn/test

Then the workbench will ask you that "What directories do you want?", if you don't want any sub-directory in your package,just type value that one of `null`，`no`, `n`, `false`.

Also, you can create multiple directories at once, just split those via comma symbol.

Even you can create the nested directories using "dot" notation:

    > What directories do you want?
    > config,resource.view,resource.lang,test

Once the package structure is generated.Workbench will automatically map the namespace of the package within the root `composer.json` file for you.

