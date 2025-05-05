<?php

namespace App\Http\Controllers\Assets;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\Request;

class ColorsController extends Controller
{
    public function createColor(Request $request)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:12',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $color = new Color();
        $color->name = $validated['name'];
        $color->code = $validated['code'];

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

            $destinationPath = public_path('uploads/colors');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $image->move($destinationPath, $imageName);

            $color->image = url('uploads/colors/' . $imageName);
        }

        $color->save();

        return response()->json(['success' => true, 'message' => 'Color created successfully', 'color' => $color], 201);
    }

    public function getAllColors(Request $request)
    {
        $colors = Color::orderBy('created_at', 'desc')->get();

        return response()->json(['success' => true, , 'message' => 'Colors fetched successfully', 'colors' => $colors], 200);
    }

    public function getColorById($id)
    {
        $color = Color::find($id);

        if (!$color) {
            return response()->json(['success' => false, 'message' => 'Color not found'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Color fetched successfully', 'color' => $color], 200);
    }
    public function updateColor(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $color = Color::find($id);

        if (!$color) {
            return response()->json(['success' => false, 'message' => 'Color not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:12',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if (isset($validated['name'])) {
            $color->name = $validated['name'];
        }

        if (isset($validated['code'])) {
            $color->code = $validated['code'];
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

            $destinationPath = public_path('uploads/colors');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $image->move($destinationPath, $imageName);

            $color->image = url('uploads/colors/' . $imageName);
        }

        $color->save();

        return response()->json(['success' => true, 'message' => 'Color updated successfully', 'color' => $color], 200);
    }
    public function deleteColor($id)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $color = Color::find($id);

        if (!$color) {
            return response()->json(['success' => false, 'message' => 'Color not found'], 404);
        }

        $color->delete();

        return response()->json(['success' => true, 'message' => 'Color deleted successfully'], 200);
    }

}
