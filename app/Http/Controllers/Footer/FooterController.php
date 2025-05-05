<?php

namespace App\Http\Controllers\Footer;

use App\Http\Controllers\Controller;
use App\Models\Footer;
use Illuminate\Http\Request;

class FooterController extends Controller
{
    public function createFooter(Request $request)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'icons' => 'nullable|array',
            'icons.*.name' => 'nullable|string',
            'icons.*.link' => 'nullable|string',
            'icons.*.icon' => 'nullable|string',
            'addresses' => 'nullable|array',
            'addresses.*.address_name' => 'nullable|string',
            'addresses.*.address_details' => 'nullable|string',
            'addresses.*.phone_number' => 'nullable|string',
        ]);

        $fullLogoUrl = null;
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoName = time() . '_' . uniqid() . '.' . $logo->getClientOriginalExtension();

            $destinationPath = public_path('uploads/footer');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $logo->move($destinationPath, $logoName);

            $fullLogoUrl = url('uploads/footer/' . $logoName);
        }


        $footer = new Footer();
        $footer->logo = $fullLogoUrl;
        $footer->icons = $data['icons'] ?? [];
        $footer->addresses = $data['addresses'] ?? [];
        $footer->save();

        return response()->json([
            'success' => true,
            'message' => 'Footer created successfully',
            'data' => $footer,
        ], 201);
    }

    public function getAllFooters()
    {
        $footers = Footer::orderBy('created_at', 'desc')->get();
        return response()->json(['success' => true, 'message' => 'Footers fetched successfully', 'data' => $footers], 200);
    }
    public function getFooterById($id)
    {
        $footer = Footer::find($id);
        if ($footer) {
            return response()->json(['success' => true, 'message' => 'Footer fetched successfully', 'data' => $footer], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'Footer not found'], 404);
        }
    }
    public function updateFooter(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $footer = Footer::find($id);
        if (!$footer) {
            return response()->json(['success' => false, 'message' => 'Footer not found'], 404);
        }

        $data = $request->validate([
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'icons' => 'nullable|array',
            'icons.*.name' => 'nullable|string',
            'icons.*.link' => 'nullable|string',
            'icons.*.icon' => 'nullable|string',
            'addresses' => 'nullable|array',
            'addresses.*.address_name' => 'nullable|string',
            'addresses.*.address_details' => 'nullable|string',
            'addresses.*.phone_number' => 'nullable|string',
        ]);

        $fullLogoUrl = null;
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoName = time() . '_' . uniqid() . '.' . $logo->getClientOriginalExtension();

            $destinationPath = public_path('uploads/footer');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $logo->move($destinationPath, $logoName);

            $fullLogoUrl = url('uploads/footer/' . $logoName);
        }

        $footer->logo = $fullLogoUrl ?? $footer->logo;
        $footer->icons = $data['icons'] ?? [];
        $footer->addresses = $data['addresses'] ?? [];
        $footer->save();

        return response()->json([
            'success' => true,
            'message' => 'Footer updated successfully',
            'data' => $footer,
        ], 200);
    }
    public function deleteFooter($id)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $footer = Footer::find($id);
        if (!$footer) {
            return response()->json(['success' => false, 'message' => 'Footer not found'], 404);
        }

        $footer->delete();

        return response()->json(['success' => true, 'message' => 'Footer deleted successfully'], 200);
    }
}
