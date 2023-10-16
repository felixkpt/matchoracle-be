<?php

namespace App\Services\Validations;

trait CommonValidations
{

    function imageRules()
    {
        return [
            request()->has('id') ? 'nullable' : 'required',
            'image',
        ];
    }
}
