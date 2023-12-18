<?php

namespace App\Interfaces;

use App\Http\Requests\ApiUserRequest;

interface APIUserInterface
{

    /**
     *  Create | Update API User (According to UserName)
     *
     * @param ApiUserRequest $request
     * @return mixed
     */
    public function registerApiUser(ApiUserRequest $request);

}
