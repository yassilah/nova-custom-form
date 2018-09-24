<?php

namespace Yassi\NovaCustomForm;

class CustomForm {
    /**
     * The form's component.
     *
     * @var string
     */
    public $component;

    /**
     * The form's create component.
     *
     * @var string
     */
    public $createComponent;

    /**
     * The form's update component.
     *
     * @var string
     */
    public $updateComponent;

    /**
     * This is the form's component to be used 
     * when creating a resource.
     * 
     * @return string
     */
    public function getCreateComponent () {
        return $this->createComponent ?? $this->component . '-create';
    }

    /**
     * This is the form's component to be used 
     * when updating a resource.
     * 
     * @return string
     */
    public function getUpdateComponent () {
        return $this->updateComponent ?? $this->component . '-edit';
    }
}
