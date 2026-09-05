<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Http\Resources\Api\V1\UserResource;

class ProfileController extends Controller
{
    public function show(): UserResource
    {
        return new UserResource(auth()->user()?->load('profile'));
    }

    public function update(UpdateProfileRequest $request): UserResource
    {
        $user = $request->user();
        $data = $request->safe()->only(['name', 'email', 'phone']);

        if ($data !== []) {
            $user->forceFill($data)->save();
        }

        $profile = $request->safe()->only(['address', 'commune', 'city']);

        if ($profile !== []) {
            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                array_merge(['city' => $user->profile?->city ?? 'Abidjan', 'country' => "Côte d'Ivoire"], $profile)
            );
        }

        return new UserResource($user->fresh()->load('profile'));
    }
}
