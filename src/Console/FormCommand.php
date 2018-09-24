<?php

namespace Yassi\NovaCustomForm\Console;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Laravel\Nova\Console\Concerns\AcceptsNameAndVendor;

class FormCommand extends Command
{
    use AcceptsNameAndVendor;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nova:form {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new form';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (! $this->hasValidNameArgument()) {
            return;
        }

        (new Filesystem)->copyDirectory(
            __DIR__.'/form-stubs',
            $this->formPath()
        );

        // Form.js replacements...
        $this->replace('{{ component }}', $this->formName(), $this->formPath().'/resources/js/form.js');

        // Form.php replacements...
        $this->replace('{{ namespace }}', $this->formNamespace(), $this->formPath().'/src/Form.stub');
        $this->replace('{{ class }}', $this->formClass(), $this->formPath().'/src/Form.stub');
        $this->replace('{{ component }}', $this->formName(), $this->formPath().'/src/Form.stub');

        (new Filesystem)->move(
            $this->formPath().'/src/Form.stub',
            $this->formPath().'/src/'.$this->formClass().'.php'
        );

        // FormServiceProvider.php replacements...
        $this->replace('{{ namespace }}', $this->formNamespace(), $this->formPath().'/src/FormServiceProvider.stub');
        $this->replace('{{ component }}', $this->formName(), $this->formPath().'/src/FormServiceProvider.stub');

        (new Filesystem)->move(
            $this->formPath().'/src/FormServiceProvider.stub',
            $this->formPath().'/src/FormServiceProvider.php'
        );

        // Form composer.json replacements...
        $this->replace('{{ name }}', $this->argument('name'), $this->formPath().'/composer.json');
        $this->replace('{{ escapedNamespace }}', $this->escapedFormNamespace(), $this->formPath().'/composer.json');

        // Register the form...
        $this->addFormRepositoryToRootComposer();
        $this->addFormPackageToRootComposer();
        $this->addScriptsToNpmPackage();

        if ($this->confirm("Would you like to install the form's NPM dependencies?", true)) {
            $this->installNpmDependencies();

            $this->output->newLine();
        }

        if ($this->confirm("Would you like to compile the form's assets?", true)) {
            $this->compile();

            $this->output->newLine();
        }

        if ($this->confirm('Would you like to update your Composer packages?', true)) {
            $this->composerUpdate();
        }
    }

    /**
     * Add a path repository for the form to the application's composer.json file.
     *
     * @return void
     */
    protected function addFormRepositoryToRootComposer()
    {
        $composer = json_decode(file_get_contents(base_path('composer.json')), true);

        $composer['repositories'][] = [
            'type' => 'path',
            'url' => './'.$this->relativeFormPath(),
        ];

        file_put_contents(
            base_path('composer.json'),
            json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    /**
     * Add a package entry for the form to the application's composer.json file.
     *
     * @return void
     */
    protected function addFormPackageToRootComposer()
    {
        $composer = json_decode(file_get_contents(base_path('composer.json')), true);

        $composer['require'][$this->argument('name')] = '*';

        file_put_contents(
            base_path('composer.json'),
            json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    /**
     * Add a path repository for the form to the application's composer.json file.
     *
     * @return void
     */
    protected function addScriptsToNpmPackage()
    {
        $package = json_decode(file_get_contents(base_path('package.json')), true);

        $package['scripts']['build-'.$this->formName()] = 'cd '.$this->relativeFormPath().' && npm run dev';
        $package['scripts']['build-'.$this->formName().'-prod'] = 'cd '.$this->relativeFormPath().' && npm run prod';

        file_put_contents(
            base_path('package.json'),
            json_encode($package, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    /**
     * Install the form's NPM dependencies.
     *
     * @return void
     */
    protected function installNpmDependencies()
    {
        $this->runCommand('npm set progress=false && npm install', $this->formPath());
    }

    /**
     * Compile the form's assets.
     *
     * @return void
     */
    protected function compile()
    {
        $this->runCommand('npm run dev', $this->formPath());
    }

    /**
     * Update the project's composer dependencies.
     *
     * @return void
     */
    protected function composerUpdate()
    {
        $this->runCommand('composer update', getcwd());
    }

    /**
     * Run the given command as a process.
     *
     * @param  string  $command
     * @param  string  $path
     * @return void
     */
    protected function runCommand($command, $path)
    {
        $process = (new Process($command, $path))->setTimeout(null);

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            $process->setTty(true);
        }

        $process->run(function ($type, $line) {
            $this->output->write($line);
        });
    }

    /**
     * Replace the given string in the given file.
     *
     * @param  string  $search
     * @param  string  $replace
     * @param  string  $path
     * @return void
     */
    protected function replace($search, $replace, $path)
    {
        file_put_contents($path, str_replace($search, $replace, file_get_contents($path)));
    }

    /**
     * Get the path to the tool.
     *
     * @return string
     */
    protected function formPath()
    {
        return base_path('nova-components/'.$this->formClass());
    }

    /**
     * Get the relative path to the form.
     *
     * @return string
     */
    protected function relativeFormPath()
    {
        return 'nova-components/'.$this->formClass();
    }

    /**
     * Get the form's namespace.
     *
     * @return string
     */
    protected function formNamespace()
    {
        return Str::studly($this->formVendor()).'\\'.$this->formClass();
    }

    /**
     * Get the form's escaped namespace.
     *
     * @return string
     */
    protected function escapedFormNamespace()
    {
        return str_replace('\\', '\\\\', $this->formNamespace());
    }

    /**
     * Get the form's class name.
     *
     * @return string
     */
    protected function formClass()
    {
        return Str::studly($this->formName());
    }

    /**
     * Get the form's resource name.
     *
     * @return string
     */
    protected function formResourceName()
    {
        return explode('-', $this->formName());
    }

    /**
     * Get the form's vendor.
     *
     * @return string
     */
    protected function formVendor()
    {
        return explode('/', $this->argument('name'))[0];
    }

    /**
     * Get the form's base name.
     *
     * @return string
     */
    protected function formName()
    {
        return explode('/', $this->argument('name'))[1];
    }
}
