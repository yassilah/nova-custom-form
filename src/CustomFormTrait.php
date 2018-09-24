<?php

namespace Yassi\NovaCustomForm;

use Laravel\Nova\Nova;

trait CustomFormTrait {

    /**
     * This method registers the custom form
     * to be used for this specific resource.
     * 
     * @return CustomForm
     */
    public static function form ($request) {
        return null;
    }
}