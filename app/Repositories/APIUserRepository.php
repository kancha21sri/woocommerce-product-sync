<?php

namespace App\Repositories;

use App\Http\Requests\ApiUserRequest;
use App\Interfaces\APIUserInterface;
use App\Traits\APIResponse;
use App\Models\ApiUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use DB;

class APIUserRepository implements APIUserInterface
{

    use APIResponse;

    /**
     * Register the API User in the System
     *
     * @param ApiUserRequest $request
     * @return JsonResponse
     */
    public function registerApiUser(ApiUserRequest $request)
    {
        DB::beginTransaction();
        try {
            $apiUser = ApiUser::where('user_name', '=', $request->input('user_name'))->first();
            if ($apiUser === null) {
                $apiUser = new ApiUser();
                $apiUser->user_name = $request->input('user_name');
                $apiUser->password = Hash::make($request->input('password'));
                $apiUser->save();
            } else {
                $apiUser->password = Hash::make($request->input('password'));
                $apiUser->save();
            }

            $token = $apiUser->createToken('auth_token')->plainTextToken;

            DB::commit();
            $tokenInformation = [
              'access_token' => $token,
              'token_type' => 'Bearer',
            ];

            return $this->success("API User Registered successfully",$tokenInformation, 200);

        } catch(\Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), $e->getCode());
        }
    }
}
