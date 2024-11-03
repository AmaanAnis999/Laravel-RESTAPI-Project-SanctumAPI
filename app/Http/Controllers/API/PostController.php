<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\BaseController as BaseController;
class PostController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['posts'] = Post::all();
        return $this->sendResponse($data, "All posts data");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validateUser = Validator::make(
            $request->all(),
            [
                'title' => 'required',
                'descriptiom' => 'required',
                'image' => 'required|mimes:png,jpg,jpeg,gif',
            ]
        );
        if ($validateUser->fails()) {
            return $this->sendResponse("Validation Error", $validateUser->errors()->all());
        }

        $img = $request->image;
        $ext = $img->getClientOriginalExtension();
        $imageName = time() . '.' . $ext;
        $img->move(public_path() . '/uploads', $imageName);

        $post = Post::create([
            'title' => $request->title,
            'descriptiom' => $request->description,
            'image' => $imageName,
        ]);
        return $this->sendResponse($post, "Post created!");

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data['post'] = Post::select(
            'id',
            'title',
            'description',
            'image',
        )->where(['id' => $id])->get();

        return $this->sendResponse($data, "Your single post");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validateUser = Validator::make(
            $request->all(),
            [
                'title' => 'required',
                'descriptiom' => 'required',
                'image' => 'required|mimes:png,jpg,jpeg,gif',
            ]
        );
        if ($validateUser->fails()) {
            return $this->sendResponse("Validation Error", $validateUser->errors()->all());
        }

        $postImage = Post::select('id', 'image' != null)->where(['id' => $id])->get();
        if ($request->image != '') {
            $path = public_path() . '/uploads';
            if ($postImage[0]->image != '' && $postImage[0]->image != null) {
                $old_file = $path . $postImage[0]->image;
                if (file_exists($old_file)) {
                    unlink($old_file);
                }
            }
            $img = $request->image;
            $ext = $img->getClientOriginalExtension();
            $imageName = time() . '.' . $ext;
            $img->move(public_path() . '/uploads', $imageName);
        } else {
            $imageName = $postImage->image;
        }

        $post = Post::where(['id' => $id])->update([
            'title' => $request->title,
            'descriptiom' => $request->description,
            'image' => $imageName,
        ]);
        return $this->sendResponse($post, "Post updated!");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $imagePath = Post::select('image')->where('id', $id)->get();
        $filePath = public_path() . '/uploads/' . $imagePath[0]['image'];
        unlink($filePath);
        $post = Post::where('id', $id)->delete();
        return $this->sendResponse($post, "Post deleted!");
    }
}
