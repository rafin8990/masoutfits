<?php

namespace App\Http\Controllers\OurStory;

use App\Http\Controllers\Controller;
use App\Models\OurStory;
use Illuminate\Http\Request;

class OurstoryController extends Controller
{
    public function getAllOurStory()
    {
        try {
            $stories = OurStory::orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Our story entries retrieved successfully.',
                'data' => $stories,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch our story entries.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getOurStoryById($id)
    {
        $story = OurStory::find($id);

        if (!$story) {
            return response()->json([
                'success' => false,
                'message' => 'Our story entry not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Our story entry retrieved successfully.',
            'data' => $story,
        ], 200);
    }

    public function createOurStory(Request $request)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeImage($request->file('image'));
        }

        $story = OurStory::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Our story entry created successfully.',
            'data' => $story,
        ], 201);
    }

    public function updateOurStory(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $story = OurStory::find($id);
        if (!$story) {
            return response()->json([
                'success' => false,
                'message' => 'Our story entry not found.',
            ], 404);
        }

        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeImage($request->file('image'));
        }

        $story->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Our story entry updated successfully.',
            'data' => $story,
        ], 200);
    }

    public function deleteOurStory($id)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $story = OurStory::find($id);
        if (!$story) {
            return response()->json([
                'success' => false,
                'message' => 'Our story entry not found.',
            ], 404);
        }

        $story->delete();

        return response()->json([
            'success' => true,
            'message' => 'Our story entry deleted successfully.',
        ], 200);
    }

    private function storeImage($image)
    {
        $fileName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
        $destinationPath = public_path('uploads/ourstory');

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        $image->move($destinationPath, $fileName);

        return url('public/uploads/ourstory/' . $fileName);
    }
}
