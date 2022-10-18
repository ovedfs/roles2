<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:posts.index')->only('index');
        $this->middleware('can:posts.store')->only('store');
        $this->middleware('can:posts.show')->only('show');
        $this->middleware('can:posts.update')->only('update');
        $this->middleware('can:posts.destroy')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts = Post::all();

        return response()->json([
            'message' => 'Listado de Posts',
            'posts' => $posts
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|unique:posts|max:200',
            'content' => 'required'
        ]);

        $post = auth()->user()->posts()->create([
            'title' => $request->title,
            'content' => $request->content
        ]);

        return response()->json([
            'message' => 'Post agregado correctamente',
            'post' => $post
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        return response()->json([
            'message' => 'Detalle de un Post',
            'post' => $post
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Post $post)
    {
        $request->validate([
            'title' => 'required|max:200|unique:posts,title,'.$post->id,
            'content' => 'required'
        ]);

        $post->update($request->all());

        return response()->json([
            'message' => 'Post actualizado correctamente',
            'post' => $post
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        $post->delete();

        return response()->json([
            'message' => 'Post eliminado correctamente'
        ]);
    }
}
