<?php

namespace App\Http\Controllers;

use App\User;
use Auth;
use Illuminate\Database\QueryException;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function loggedin()
    {
        // Check to see if we are logged in via remember me cookie
        if (!Auth::check()) {
            // If not then return false
            return response([
                'loggedin' => false
            ], 400);
        } else {
            // If so then return true as we still have a valid session cookie
            return response([
                'loggedin' => true
            ], 200);
        }
    }

    public function login()
    {
        // Simulate post data
        $post_data = [
            'name' => 'Mr. Admin',
            'email' => 'admin@extjstips.com',
            'password' => 'password'
        ];

        // Remember token
        $remember = true;

        // Attempt to log in
        if (Auth::attempt($post_data, $remember)) {
            // If login is successful return true and user data
            return response([
                'loggedin' => true,
                'data' => Auth::user()
            ], 200);
        } else {
            // Login attempt failed so check if the user exists
            $user = User::whereEmail($post_data['email'])->first();
            if (count($user) === 0) {
                // If user does not exist then return false
                return response([
                    'user' => false,
                    'message' => 'User does not exist'
                ], 400);
            } else {
                // If user does exist then check the password.  If the password doesn't match then return false
                if (!Hash::check($post_data['password'], $user->password)) {
                    return response([
                        'password' => false,
                        'message' => 'Wrong password'
                    ], 400);
                } else {
                    // It's all jacked up
                    return response([
                        'message' => 'Server error'
                    ], 500);
                }
            }
        }
    }

    public function register()
    {
        // Simulate post data
        $post_data = [
            'name' => 'Mr. Admin',
            'email' => 'admin@extjstips.com',
            'password' => bcrypt('password')
        ];
        // Create a new User model with the post data
        $user = new User($post_data);
        // Try to save the user
        try {
            $user->save();
        } catch (QueryException $e) {
            // The email field in the users table has a unique index so it will throw an error
            // if there is a duplicate
            if (preg_match('/Duplicate entry/', $e->getMessage())) {
                return response([
                    'message' => 'User Exists'
                ], 400);
            } else {
                return response([
                    'message' => $e->getMessage()
                ], 500);
            }
        }
        // If the user create was a success return Accepted and loggedin false.
        if ($user->exists) {
            return response([
                'loggedin' => false
            ], 201);
        } else {
            return response([
                'message' => 'Server error'
            ], 500);
        }
    }

    public function logout()
    {
        Auth::logout();
        if (!Auth::check()) {
            // If not then return false
            return response([
                'loggedin' => false
            ], 200);
        }
    }
}