<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserDestroyRequest;
use App\Http\Controllers\Controller;
use App\User;
use App\Events\UserStoreBroadcastEvent;
use App\Events\UserDestroyBroadcastEvent;

class UserController extends Controller
{
    /**
     * User Eloquent Data Store
     *
     * @var \App\User
     */
    protected $user;

    /**
     * @param \App\User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = $this->user->latest()->get();

        return response($users);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\UserStoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserStoreRequest $request)
    {
        $user = $this->user->create($request->only(['name', 'email', 'password']));

        event(new UserStoreBroadcastEvent($user));

        return response($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Http\Requests\UserDestroyRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserDestroyRequest $request, $id)
    {
        $user = $this->user->find($id);
        $this->user->destroy($id);

        event(new UserDestroyBroadcastEvent($user->toArray()));

        return response($user);
    }
}
