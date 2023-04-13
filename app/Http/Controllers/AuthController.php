<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\StoreImageRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    // new user registeration
    public function register(Request $request)
    {
        $fields = $request->validate([
            'userName' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed'
        ]);

        $user = User::create([
            'userName' => $fields['userName'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password'])
        ]);

        // $token = $user->createToken('myapptoken')->plainTextToken;

        $response = [
            'user' => $user,
            // 'token' => $token
        ];

        return response($response, 201);
    }

    /**
     * user login and token creation
     */
    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        // Check email
        $user = User::where('email', $fields['email'])->first();

        // Check password
        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response([
                'message' => 'Bad creds'
            ], 401);
        }

        $token = $user->createToken('myapptoken')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response($response, 201);
    }



    /**
     * get all users
     */
    public function index()
    {
        $users = User::with('urls')->get()->map(function ($user) {
            $urls = $user->urls->map(function ($url) {
                return [
                    'id' => $url->id,
                    'name' => $url->name,
                    'link' => $url->link,
                    'isActive' => $url->isActive,
                    'description' => $url->description,
                ];
            });
            return [
                'id_user' => $user->id,
                'userName' => $user->userName,
                'email' => $user->email,
                'image' => $user->image,
                'description' => $user->description,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'url' => $urls,
            ];
        });

        return response()->json($users);
    }
    /**
     * get specified user by id
     */
    public function show($id)
    {
        $user = User::with('urls')->findOrFail($id);
        $urls = $user->urls->map(function ($url) {
            return [
                'id' => $url->id,
                'name' => $url->name,
                'link' => $url->link,
                'isActive' => $url->isActive,
                'description' => $url->description,
            ];
        });
        return response()->json([
            'id_account' => $user->id,
            'userName' => $user->userName,
            'email' => $user->email,
            'image' => $user->image,
            'description' => $user->description,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'url' => $urls,
        ]);
    }

    /**
     * search user by name
     */
    public function search(string $name)
    {
        return User::where('userName', 'like', '%' . $name . '%')->get();
    }

    /**
     * create new url
     */
    public function url_post(Request $request, $id)
    {
        $name = $request->input('name');
        $link = $request->input('link');
        $isActive = $request->input('isActive');
        $description = $request->input('description');

        if (!$name || !$link) {
            return response()->json([
                'message' => 'Required parameters missing',
            ], 400);
        }

        $user = User::findOrFail($id);
        $user->urls()->create([
            "name" => $name,
            "link" => $link,
            "isActive" => $isActive,
            "description" => $description,
        ]);

        return response()->json([
            'message' => 'New URL added successfully',
        ]);
    }

    /**
     * change the information of user by id
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        $user->update($request->all());
        return $user;
    }

    /**
     * change the informations of url by id
     */
    public function url_update(Request $request, string $id, string $url_id)
    {
        $user = User::findOrFail($id);
        $url = $user->urls()->find($url_id);

        if (!$url) {
            return response()->json(['message' => 'URL not found'], 404);
        }

        $url->name = request()->input('name', $url->name);
        $url->link = request()->input('link', $url->link);
        $url->isActive = request()->input('isActive', $url->isActive);
        $url->description = request()->input('description', $url->description);

        $url->save();

        return response()->json([
            'message' => 'URL updated successfully',
            $url
        ]);
    }


    /**
     * delete selected user by id
     */
    public function destroy(string $id)
    {
        return User::destroy($id);
    }

    /**
     * delete selected url by id
     */
    public function url_destroy($id, $url_id)
    {
        $user = User::findOrFail($id);
        $url = $user->urls()->find($url_id);
        if (!$url) {
            return response()->json(['message' => 'Url not found'], 404);
        }
        $url->delete();
        return response()->json(['message' => 'Url deleted successfully'], 200);
    }

    /**
     * user logout and token destroy 
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return [
            'message' => 'Logged out'
        ];
    }

    // View Uploaded Image
    public function indexWeb(Request $request, $id)
    {
        // Get the user with the given ID
        $user = User::findOrFail($id);

        // Check if the user has an image
        if ($user->image) {
            // Get the image path
            $imagePath = storage_path('app/public/' . $user->image);

            // Check if the image file exists
            if (file_exists($imagePath)) {
                // Read the image file contents
                $imageData = file_get_contents($imagePath);

                // Get the image MIME type
                $mimeType = mime_content_type($imagePath);

                // Create the response object with image contents and MIME type
                $response = response($imageData, 200)
                    ->header('Content-Type', $mimeType);

                // Return the response object
                return $response;
            }
        }
        // If the user does not have an image, return a 404 error response
        return response()->json(['error' => 'Image not found'], 404);
    }

    // Store Image
    public function storeImage(StoreImageRequest $request, string $id)
    {
        try {
            // create new ImageName
            $imageName = Str::random(12) . '.' . $request->image->getClientOriginalExtension();

            // save the image in to public disk
            $request->file('image')->storeAs('public', $imageName);

            // Find the user and refresh the image field
            $user = User::findOrFail($id);
            $user->image = $imageName;
            $user->save();

            // return Json
            return response()->json([
                'message' => 'Image succesfully added! 👍'
            ], 200);
        } catch (\Exception $e) {

            // return Json
            return response()->json([
                'message' => 'something went really wrong! 👎'
            ], 500);
        }
    }
}
