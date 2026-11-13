<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * {tenant_prefix} is only for tenancy middleware / URL generation.
     * If left in $parameters, callAction's array_values() shifts real args
     * (e.g. AssetController::css gets "local" instead of "login.css").
     */
    public function callAction($method, $parameters)
    {
        unset($parameters['tenant_prefix']);

        return parent::callAction($method, $parameters);
    }
}

