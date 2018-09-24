<?php

namespace Yassi\NovaCustomForm;

use Laravel\Nova\Nova;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;

class NovaCustomFormServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Nova::serving(function (ServingNova $event) {
            Nova::provideToScript([
                'customForms' => $this->attachCustomForms($this->filterResources())
            ]);
            Nova::script('nova-custom-forms', __DIR__ . '/../dist/js/nova-custom-forms.js');
        });
    }

    /**
     * This method filters the resources that
     * user NovaCustomForm. 
     * 
     * @return array
     */
    private function filterResources () {
        return collect(Nova::availableResources(request()))->filter(function ($resource) {
            return method_exists($resource, 'form') ? $resource::form(request()) : false;
        });
    }

    /**
     * This method maps the available custom forms
     * and keys them with their corresponding resource.
     * 
     * @return array
     */
    private function attachCustomForms (array $resources) {
        return $resources->mapWithKeys(function ($resource) {
            return [ 
                (new $resource::$model)->getTable() => [
                    'create' => $resource::form(request())->getCreateComponent(),
                    'edit' => $resource::form(request())->getUpdateComponent()
                ]
            ];
        })
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([
            Console\FormCommand::class
        ]);
    }
}
