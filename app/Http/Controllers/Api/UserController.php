<?php

namespace App\Http\Controllers\Api;


use App\Filters\UserRoleFilter;
use App\Http\Requests\UserDestroyRequest;
use App\Http\Requests\UserIndexRequest;
use App\Http\Requests\UserRoleIndexRequest;
use App\Http\Requests\UserShowRequest;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserRoleResource;
use App\Http\Resources\UserResource;
use App\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\QueryBuilder\Filter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * Class UserController
 * @package App\Http\Controllers\Api
 * @resource User
 */
class UserController extends Controller
{
    /**
     * @queryParams
     * @param UserIndexRequest $request
     * @return ResourceCollection
     */
    public function index(UserIndexRequest $request) : ResourceCollection
    {
        $users = QueryBuilder::for(User::class)
            ->allowedFilters([
                'name',
                'email',
                Filter::custom('role', UserRoleFilter::class)
            ])
            ->jsonPaginate()
        ;

        return UserResource::collection($users);
    }

    /**
     * @param UserStoreRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function store(UserStoreRequest $request)
    {
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make(str_random(64)),
        ]);

        if ($request->input('roles')) {
            $roles = array_map(function ($role) {
                return Role::whereName($role)->first();
            }, $request->input('roles'));

            $user->syncRoles($roles);
        }


        if ($request->input('send_invite')) {
            // send user invitation email
        }

        return response(new UserResource($user), 201);
    }

    /**
     * @param UserShowRequest $request
     * @param User $user
     * @return UserResource
     */
    public function show(UserShowRequest $request, User $user)
    {
        return new UserResource($user);
    }

    /**
     * @param UserUpdateRequest $request
     * @param User $user
     * @return UserResource
     */
    public function update(UserUpdateRequest $request, User $user)
    {
        if ($request->input('roles')) {
            $roles = array_map(function ($role) {
                return Role::whereName($role)->first();
            }, $request->input('roles'));

            $user->syncRoles($roles);
        }

        return new UserResource($user);
    }

    /**
     * @param UserDestroyRequest $request
     * @param User $user
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(UserDestroyRequest $request, User $user)
    {
        $user->delete();

        return response(null, 204);
    }

    /**
     * @param UserRoleIndexRequest $request
     * @return AnonymousResourceCollection
     */
    public function roleIndex(UserRoleIndexRequest $request) : AnonymousResourceCollection
    {
        return UserRoleResource::collection(Role::jsonPaginate());
    }
}