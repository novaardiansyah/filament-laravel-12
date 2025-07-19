<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageResource\NotifContactMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TestingController extends Controller
{
  public function index(Request $request)
  {
    $preview = (bool) $request->input('preview', 0);

    $data = [
      'email'   => 'novaardiansyah78@gmail.com',
      'subject' => 'Notifikasi: Pesan masuk baru dari situs web',
    ];

    if (!$preview) {
      Mail::to($data['email'])->queue(new NotifContactMail($data));
      echo 'Email has been queued for sending.';
    }

    $process = new NotifContactMail($data);
    return $process->render();
  }
}
