<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $resource;

    public static $resourceModels = [
        'spectra' => 'Spectra',
    ];


    public function __Construct() {
        $uri = request()->path();

        $this->resource = explode('/', $uri)[0];
        
        //For the RESTFUL API version 1 (api/v1/) requests
        /*if(Str::contains($uri, 'api/v1/direct/'))
        {
            // we get like api/v1/user/direct/1
            // the resource is at index 3 api/v1/direct/{user}/1
            // resource is "user"
            $this->resource = explode('/', $uri)[3];
        }
        elseif(Str::contains($uri, 'api/v1/'))
        {
            // we get like api/v1/user/1
            // the resource is at index 2 api/v1/{user}/1
            // resource is "user"
            $this->resource = explode('/', $uri)[2];
        }
        //For normal Website requests
        else
        {
            $this->resource = explode('/', $uri)[0];
        }*/
    }

}
