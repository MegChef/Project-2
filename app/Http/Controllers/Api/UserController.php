<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Services\AuthService;

class UserController extends Controller
{
    // register
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => 'user'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'userId' => $user->id,
        ], 201);
    }

    // login
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'role' => $user->role,
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    // logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    // admin gets all users
    public function getUsers()
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Admins only'
            ], 403);
        }

        $users = User::all();

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    // admin delets a user
    public function adminDelete($id)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Admins only'
            ], 403);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }

    // change password
    public function changePassword(Request $request)
    {
        $request->validate([
            'oldPassword' => 'required|string',
            'newPassword' => 'required|string|min:6',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->oldPassword, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Old password incorrect'
            ], 400);
        }

        $user->password = Hash::make($request->newPassword);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }

    // delete self
    public function deleteSelf()
    {
        $user = auth()->user();

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Your account has been deleted'
        ]);
    }
}
