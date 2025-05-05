<?php

namespace App\Http\Controllers\Assets;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function createTag(Request $request)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $tag = new Tag();
        $tag->name = $request->input('name');
        $tag->description = $request->input('description');
        $tag->save();

        return response()->json(['success' => true, 'message' => 'Tag created successfully', 'data' => $tag], 201);
    }

    public function getAllTags(Request $request)
    {
        $tags = Tag::orderBy('created_at', 'desc')->get();

        return response()->json(['success' => true, 'message' => 'Tags fetched successfully', 'data' => $tags], 200);
    }
    public function getTagById($id)
    {
        $tag = Tag::find($id);

        if (!$tag) {
            return response()->json(['success' => false, 'message' => 'Tag not found'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Tag fetched successfully', 'data' => $tag], 200);
    }
    public function updateTag(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $tag = Tag::find($id);

        if (!$tag) {
            return response()->json(['success' => false, 'message' => 'Tag not found'], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $tag->name = $request->input('name');
        $tag->description = $request->input('description');
        $tag->save();

        return response()->json(['success' => true, 'message' => 'Tag updated successfully', 'data' => $tag], 200);
    }
    public function deleteTag($id)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $tag = Tag::find($id);

        if (!$tag) {
            return response()->json(['success' => false, 'message' => 'Tag not found'], 404);
        }

        $tag->delete();

        return response()->json(['success' => true, 'message' => 'Tag deleted successfully'], 200);
    }
}
