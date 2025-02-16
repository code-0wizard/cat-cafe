<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBlogRequest;
use App\Http\Requests\Admin\UpdateBlogRequest;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminBlogController extends Controller
{
    // ブログ一覧画面
    public function index()
    {
        $blogs = Blog::latest('updated_at')->simplePaginate(10);
        return view('admin.blogs.index', ['blogs' => $blogs]);
    }


    // ブログ投稿画面
    public function create()
    {
        return view('admin.blogs.create');
    }

    // ブログ投稿処理
    public function store(StoreBlogRequest $request)
    {
        $savedImagePath = $request->file('image')->store('blogs', 'public');
        $blog = new Blog($request->validated());
        $blog->image = $savedImagePath;
        $blog->save();

        // 下記処理でもOK
        // $validated = $request->validated();
        // $validated['image'] = $request->file('image')->store('blogs', 'public');
        // Blog::create($validated);
        
        return to_route('admin.blogs.index')->with('success', 'ブログを投稿しました');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
			$blog = Blog::findorFail($id);
			return view('admin.blogs.edit', ['blog' => $blog]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBlogRequest $request, string $id)
    {
        $blog = Blog::findorFail($id);
				$updateData = $request->validated();
				
				// 画像を変更する場合
				if($request->has('image')) {
					// 変更前の画像を削除
					Storage::disk('public')->delete($blog->image);
					// 変更後の画像をアップロード、保存パスを更新対象データにセット
					$updateData['image'] = $request->file('image')->store('blogs', 'public');
				}
				$blog->update($updateData);

				return to_route('admin.blogs.index')->with('success', 'ブログを更新しました');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $blog = Blog::findorFail($id);
				$blog->delete();
				Storage::disk('public')->delete($blog->image);

				return to_route('admin.blogs.index')->with('success', 'ブログを削除しました');
    }
}