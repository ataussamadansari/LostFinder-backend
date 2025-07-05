<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{

    public function saveProfile(Request $request)
    {
        $user = auth()->user();

        // Step 1: Common Validation
        $validator = Validator::make($request->all(), [
            'role' => 'required|in:driver,rider',
            'name' => 'required|string',
            'email' => 'nullable|email',
            'address' => 'required|string',
            'gender' => 'required_if:role,rider|in:male,female,other',
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

            // Driver-specific fields
            'vehicle_number' => 'required_if:role,driver',
            'vehicle_type' => 'required_if:role,driver',
            'vehicle_model' => 'required_if:role,driver',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Step 2: Save profile image
        if ($request->hasFile('profile_image')) {
            $imagePath = $request->file('profile_image')->store('profiles', 'public');
            $user->profile_image = $imagePath;
        }

        // Step 3: Update User Table
        $user->update([
            'role' => $request->role,
            'name' => $request->name,
            'email' => $request->email,
            'address' => $request->address,
            'gender' => $request->gender,
        ]);

        // Step 4: If role is driver → save vehicle info
        if ($request->role === 'driver') {
            $user->driverProfile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'vehicle_number' => $request->vehicle_number,
                    'vehicle_type' => $request->vehicle_type,
                    'vehicle_model' => $request->vehicle_model,
                ]
            );
        }

        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully',
            'user' => $user->load('driverProfile'),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        // Step 1: Flexible Validation
        $validator = Validator::make($request->all(), [
            'role' => 'sometimes|in:driver,rider',
            'name' => 'sometimes|string',
            'email' => 'sometimes|nullable|email',
            'address' => 'sometimes|string',
            'gender' => 'sometimes|in:male,female,other',
            'profile_image' => 'sometimes|image|mimes:jpg,jpeg,png|max:2048',

            // Driver fields (only if provided and role is driver)
            'vehicle_number' => 'sometimes|required_if:role,driver',
            'vehicle_type' => 'sometimes|required_if:role,driver',
            'vehicle_model' => 'sometimes|required_if:role,driver',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Step 2: Handle profile image
        if ($request->hasFile('profile_image')) {
            $imagePath = $request->file('profile_image')->store('profiles', 'public');
            $user->profile_image = $imagePath;
        }

        // Step 3: Update user fields dynamically
        $fields = ['role', 'name', 'email', 'address', 'gender'];
        foreach ($fields as $field) {
            if ($request->has($field)) {
                $user->{$field} = $request->{$field};
            }
        }
        $user->save();

        // Step 4: If driver, update driverProfile too
        if ($user->role === 'driver') {
            $driverData = [];
            if ($request->has('vehicle_number')) $driverData['vehicle_number'] = $request->vehicle_number;
            if ($request->has('vehicle_type')) $driverData['vehicle_type'] = $request->vehicle_type;
            if ($request->has('vehicle_model')) $driverData['vehicle_model'] = $request->vehicle_model;

            if (!empty($driverData)) {
                $user->driverProfile()->updateOrCreate(
                    ['user_id' => $user->id],
                    $driverData
                );
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully',
            'user' => $user->load('driverProfile'),
        ]);
    }
}
