<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApiUserRequest;
use App\Interfaces\APIUserInterface;

class ApiUserAuthController extends Controller
{
    /**
     * @var APIUserInterface
     */
    private APIUserInterface $apiuserInterface;

    /**
     * @param APIUserInterface $apiuserInterface
     */
    public function __construct(APIUserInterface $apiuserInterface)
    {
        $this->apiuserInterface = $apiuserInterface;
    }

    /**
     * Register the API Users
     *
     * @param ApiUserRequest $request
     * @return mixed
     */
    public function register(ApiUserRequest $request)
    {
        return $this->apiuserInterface->registerApiUser($request);
    }

}
