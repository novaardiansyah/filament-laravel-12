<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageResource\ReplyContactMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TestingController extends Controller
{
  public function index(Request $request)
  {
    $preview = (bool) $request->input('preview', 0);

    $data = [
      'name'    => textCapitalize('John Doe'),
      'email'   => 'novaardiansyah78@gmail.com',
      'subject' => 'Terima Kasih Telah Menghubungi Saya',
    ];

    if (!$preview) {
      Mail::to($data['email'])->queue(new ReplyContactMail($data));
      echo 'Email has been queued for sending.';
    }

    $process = new ReplyContactMail($data);
    return $process->render();
  }
}
