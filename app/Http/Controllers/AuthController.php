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
                    'active' => $url->isActive,
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
                'active' => $url->isActive,
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

        if (!$name || !$link) {
            return response()->json([
                'message' => 'Required parameters missing',
            ], 400);
        }

        $user = User::findOrFail($id);
        $user->urls()->create([
            "name" => $name,
            "link" => $link,
            "isActive" => $isActive
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

        $url->update($request->all());
        return $url;
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

    // View File To Upload Image
    public function indexWeb()
    {
        return view('image-form');
    }

    // Store Image
    public function storeImage(StoreImageRequest $request, string $id)
    {
        try {
            $imageName = time() . "." . $request->image->extension();

            //Create Post

            $user = User::findOrFail($id);
            $user->update($request->all());

            // User::create([
            //     'image' => $imageName
            // ]);

            //save Image in storage folder
            Storage::disk('public')->put($imageName, file_get_contents($request->image));

            //return Json response
            return response()->json([
                'message' => 'Image succesfully added! 👍'
            ], 200);
        } catch (\Exception $e) {

            //Return Json Response
            return response()->json([
                'message' => 'something went really wrong! 👎'
            ], 500);
        }


        //  $request->validate([
        //      'image' => 'required|image|mimes:png,jpg,jpeg|max:2048'
        //  ]);

        //  $imageName = time() . '.' . $request->image->extension();

        //  // Public Folder
        //  $request->image->move(public_path('images'), $imageName);

        //  // //Store in Storage Folder
        //  // $request->image->storeAs('images', $imageName);

        //  // // Store in S3
        //  // $request->image->storeAs('images', $imageName, 's3');

        //  //Store Image in DB 


        //  return back()->with('success', 'Image uploaded Successfully!')
        //      ->with('image', $imageName);
    }
}
