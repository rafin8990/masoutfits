<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;

class SliderController extends Controller
{
    public function createSlider(Request $request)
    {
        $user = auth()->user();
        if (!$user->role == 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|string|in:active,inactive',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

            $destinationPath = public_path('uploads/sliders');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $image->move($destinationPath, $imageName);

            $fullImageUrl = url('public/uploads/sliders/' . $imageName);
            $validatedData['image'] = $fullImageUrl;
        }

        Slider::create($validatedData);

        return response()->json(['success' => true, 'message' => 'Slider created successfully', 'data' => $validatedData], 201);
    }

    public function getAllSliders()
    {
        $sliders = Slider::orderBy('created_at', 'desc')->get();
        return response()->json(['success' => true, 'message' => 'Sliders fetched successfully', 'data' => $sliders], 200);
    }

    public function getSliderById($id)
    {
        $slider = Slider::find($id);
        if ($slider) {
            return response()->json(['success' => true, 'message' => 'Slider fetched successfully', 'data' => $slider], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'Slider not found'], 404);
        }
    }

    public function updateSlider(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user->role == 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $slider = Slider::find($id);
        if (!$slider) {
            return response()->json(['success' => false, 'message' => 'Slider not found'], 404);
        }

        $validatedData = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'image' => 'sometimes|required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'sometimes|required|string|in:active,inactive',
        ]);

        if ($request->hasFile('image')) {
            if ($slider->image) {
                $oldImagePath = public_path(parse_url($slider->image, PHP_URL_PATH));
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

            $destinationPath = public_path('uploads/sliders');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $image->move($destinationPath, $imageName);

            $fullImageUrl = url('public/uploads/sliders/' . $imageName);
            $validatedData['image'] = $fullImageUrl;
        }

        $slider->update($validatedData);

        return response()->json(['success' => true, 'message' => 'Slider updated successfully', 'data' => $slider], 200);
    }
    public function deleteSlider($id)
    {
        $user = auth()->user();
        if (!$user->role == 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $slider = Slider::find($id);
        if (!$slider) {
            return response()->json(['success' => false, 'message' => 'Slider not found'], 404);
        }

        if ($slider->image) {
            $imagePath = public_path(parse_url($slider->image, PHP_URL_PATH));
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $slider->delete();

        return response()->json(['success' => true, 'message' => 'Slider deleted successfully'], 200);
    }
}
