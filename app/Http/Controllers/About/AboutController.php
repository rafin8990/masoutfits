<?php

namespace App\Http\Controllers\About;

use App\Http\Controllers\Controller;
use App\Models\About;
use Illuminate\Http\Request;

class AboutController extends Controller
{
    public function createAbout(Request $request)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $image = $request->file('image');
        $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

        $destinationPath = public_path('uploads/about');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        $image->move($destinationPath, $imageName);

        $fullImageUrl = url('public/uploads/about/' . $imageName);

        $about = new About();
        $about->title = $data['title'];
        $about->description = $data['description'];
        $about->image = $fullImageUrl;
        $about->save();

        return response()->json(['message' => 'About section created successfully', 'data' => $about], 201);
    }

    public function getAllAbout()
    {
        try {
            $about = About::orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'message' => 'About sections fetched successfully',
                'data' => $about,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while fetching the about sections.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAboutById($id)
    {
        $about = About::find($id);
        if ($about) {
            return response()->json(['success' => true, 'message' => 'About section fetched successfully', 'data' => $about], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'About section not found'], 404);
        }
    }
    public function updateAbout(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $about = About::find($id);
        if (!$about) {
            return response()->json(['success' => false, 'message' => 'About section not found'], 404);
        }

        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

            $destinationPath = public_path('uploads/about');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $image->move($destinationPath, $imageName);

            $fullImageUrl = url('public/uploads/about/' . $imageName);
            $data['image'] = $fullImageUrl;
        }

        $about->update($data);

        return response()->json(['success' => true, 'message' => 'About section updated successfully', 'data' => $about], 200);
    }
    public function deleteAbout($id)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $about = About::find($id);
        if (!$about) {
            return response()->json(['success' => false, 'message' => 'About section not found'], 404);
        }

        $about->delete();

        return response()->json(['success' => true, 'message' => 'About section deleted successfully'], 200);
    }
}
