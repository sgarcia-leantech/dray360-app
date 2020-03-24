<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\User;

class AuthenticationController extends Controller
{

    /**
     * Create new user account
     *
     * @param  [Request] $request
     * @param  [string] $request->email
     * @param  [string] $request->password
     * @param  [string] $request->name
     * @return [string] success message
     */
    public function signup(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string'
        ]);

        $user = new User([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        $user->save();

        return response()->json(['message' => 'Successfully created user!'], 201);
    }


    /**
     * Login user
     *
     * @param  [Request] $request
     * @param  [string] $request->email
     * @param  [string] $request->password
     * @return [response] success or failure message+code
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            // Authentication passed...
            return response()->json(['message' => 'Login successful'], 200);
        } else {
            return response()->json(['message' => 'Not authorized'], 401);
        }
    }


    /**
     * Logout user
     *
     * @return [response] success or failure message+code
     */
    public function logout()
    {
        Auth::logout();
        return response()->json(['message' => 'Logged Out'], 200);
    }


    /**
     * Get the currently authenticated User
     *
     * TODO: NOT WORKING. FIX THIS.
     *
     * @param  [Request] $request
     * @param  [integer] $request->id
     * @return [json] user object
     */
    public function user(Request $request)
    {
        $request->validate(['id' => 'required|integer']);
        $user = User::with('roles.permissions')->where('id', '=', $request->id)->firstOrFail();
        return response()->json($user);
    }

    public function user_orig_does_not_work(Request $request)
    {
        $authUser = $request->user();
        $user = User::with('roles.permissions')->where('id', '=', $authUser->id)->firstOrFail();
        return response()->json($user);
    }

}
