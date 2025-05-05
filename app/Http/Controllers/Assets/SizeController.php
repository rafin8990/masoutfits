<?php

namespace App\Http\Controllers\Assets;

use App\Http\Controllers\Controller;
use App\Models\Size;
use Illuminate\Http\Request;

class SizeController extends Controller
{
    public function createSize(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $size = new Size();
        $size->name = $request->name;
        $size->save();

        return response()->json(['success' => true, 'message' => 'Size created successfully', 'data' => $size], 201);
    }

    public function getAllSizes(Request $request)
    {
        $sizes = Size::orderBy('created_at', 'desc')->get();

        return response()->json(['success' => true, 'message' => 'Sizes fetched successfully', 'data' => $sizes], 200);
    }
    public function getSizeById($id)
    {
        $size = Size::find($id);

        if (!$size) {
            return response()->json(['success' => false, 'message' => 'Size not found'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Size fetched successfully', 'data' => $size], 200);
    }
    public function updateSize(Request $request, $id)
    {
        $size = Size::find($id);

        if (!$size) {
            return response()->json(['success' => false, 'message' => 'Size not found'], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $size->name = $request->name;
        $size->save();

        return response()->json(['success' => true, 'message' => 'Size updated successfully', 'data' => $size], 200);
    }
    public function deleteSize($id)
    {
        $size = Size::find($id);

        if (!$size) {
            return response()->json(['success' => false, 'message' => 'Size not found'], 404);
        }

        $size->delete();

        return response()->json(['success' => true, 'message' => 'Size deleted successfully'], 200);
    }
}
    