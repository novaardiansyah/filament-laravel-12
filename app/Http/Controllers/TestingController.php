<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TestingController extends Controller
{
  public function index()
  {
    return view('testing.index');
  }

  public function upload(Request $request)
  {
    $file = request('avatar');
    $filename = $file->getClientOriginalName();
    $extension = $file->getClientOriginalExtension();

    $res = Storage::disk('s3')->putFileAs(
      'avatars',
      $file,
      now()->format('YmdHis') . '.' . $extension,
      [
        'visibility' => 'public',
      ]
    );

    $url = Storage::disk('s3')->url($res);

    return response()->json([
      'message'  => 'File uploaded successfully',
      'filename' => $filename ?? null,
      'url'      => $url ?? null
    ]);
  }
}
