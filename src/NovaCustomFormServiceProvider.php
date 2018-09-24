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
                'customForms' => collect(Nova::availableResources(request()))
                ->filter(function ($resource) {
                    return $resource::form();
                })
                ->mapWithKeys(function ($resource) {
                    return [ 
                        (new $resource::$model)->getTable() => [
                            'create' => $resource::form(request())->getCreateComponent(),
                            'edit' => $resource::form(request())->getUpdateComponent()
                        ]
                    ];
                })
            ]);
            Nova::script('nova-custom-forms', __DIR__ . '/../dist/js/nova-custom-forms.js');
        });
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
