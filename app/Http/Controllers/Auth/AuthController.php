<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function createUser(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:15',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'password' => 'required|string|confirmed',
            'role' => 'required|string',
        ]);
        $validatedData['password'] = bcrypt($validatedData['password']);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

            $destinationPath = public_path('uploads/users');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $image->move($destinationPath, $imageName);

            $fullImageUrl = url('uploads/users/' . $imageName);
            $validatedData['image'] = $fullImageUrl;
        }

        // dd($validatedData);
// return $validatedData;
        $user = User::create($validatedData);
        $token = $user->createToken($request->name);
        if ($user) {
            return response()->json([
                'message' => 'User created successfully',
                'user' => $user,
                'token' => $token->plainTextToken,
            ], 201);
        } else {
            return response()->json(['message' => 'Failed to create user'], 500);
        }
    }

    public function login(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string',
        ]);
        $user = User::where('email', $validatedData['email'])->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if (!$user || !Hash::check($request->password, $user->password)) {
            return [
                'message' => 'The provided credentials are incorrect.'
            ];
        }
        $token = $user->createToken($user->name);

        return response()->json([
            'success' => true,
            'message' => 'User logged in successfully',
            'user' => $user,
            'token' => $token->plainTextToken,
        ], 200);
    }

    public function updateUser(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }
        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:15',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        if ($request->has('password')) {
            $validatedData['password'] = bcrypt($request->input('password'));
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

            $destinationPath = public_path('uploads/users');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $image->move($destinationPath, $imageName);

            $fullImageUrl = url('uploads/users/' . $imageName);
            $validatedData['image'] = $fullImageUrl;
        }

        if ($user->update($validatedData)) {
            return response()->json(['success' => true, 'message' => 'User updated successfully', 'user' => $user], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'Failed to update user'], 500);
        }
    }

    public function logout(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'You must Login first'], 404);
        }
        $user->tokens()->delete();
        return response()->json(['message' => 'Logout successful'], 200);
    }
    public function deleteUser(Request $request,$id)
    {
        $loginUser = auth()->user();
        if (!$loginUser) {
            return response()->json(['message' => 'You must login first'], 404);
        }
        if ($loginUser->role !== 'admin') {
            return response()->json(['message' => 'You are not authorized to delete this user'], 403);
        }
        $user = User::find($id);
        if (!$user) {
            return response()->json(['success' => false,'message' => 'User not found'], 404);
        }

        if ($user->delete()) {
            return response()->json(['success' => true,'message' => 'User deleted successfully'], 200);
        } else {
            return response()->json(['success' => false,'message' => 'Failed to delete user'], 500);
        }
    }
    public function getUser(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }
        return response()->json(['success' => true, 'user' => $user], 200);
    }
    public function getAllUsers(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Please Login first'], 404);
        }

        if ($user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'you are not authorized to access this route'], 403);
        }

        $users = User::all();
        return response()->json(['success' => true, 'users' => $users], 200);
    }
    public function getUserById(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Please Login first'], 404);
        }

        if ($user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'you are not authorized to access this route'], 403);
        }

        $givenUser = User::find($id);
        if (!$givenUser) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }
        return response()->json(['success' => true, 'user' => $givenUser], 200);
    }
    public function getUserByEmail(Request $request, $email)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Please Login first'], 404);
        }

        if ($user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'you are not authorized to access this route'], 403);
        }
        $givenUser = User::where('email', $email)->first();
        if (!$givenUser) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }
        return response()->json(['success' => true, 'user' => $givenUser], 200);
    }

    public function changePassword(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Please Login first'], 404);
        }
        $validatedData = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|confirmed',
        ]);

        if (!$user || !Hash::check($validatedData['current_password'], $user->password)) {
            return response()->json(['success' => false, 'message' => 'The provided credentials are incorrect.'], 401);
        }
        $user->password = bcrypt($validatedData['new_password']);

        $user->save();

        return response()->json(['success' => true, 'message' => 'Password changed successfully'], 200);
    }
}


