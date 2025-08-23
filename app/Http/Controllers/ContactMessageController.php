<?php

namespace App\Http\Controllers;

use App\Jobs\ContactMessageResource\StoreMessageJob;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Validator;
use \Illuminate\Validation\Validator AS validationValidator;
use Illuminate\Support\Facades\Http;

class ContactMessageController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    //
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    //
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $validator = $this->_set_validator($request);

    if ($validator->fails()) {
      return response()->json([
        'message' => 'Invalid Form!',
        'errors'  => $validator->errors()
      ], 422);
    }

    $validated = $validator->validate();

    $check_captcha = Http::asForm()->post(config('services.cloudflare.turnstile.site_url'), [
      'secret'   => config('services.cloudflare.turnstile.secret_key'),
      'response' => $validated['captcha_token'],
    ])->json();
    
    if (!$check_captcha['success']) {
      return response()->json([
        'message' => 'You have entered an invalid captcha, please try again!',
      ], 422);
    }

    $contactMessage = ContactMessage::where('email', $validated['email'])
      ->where('created_at', '>=', now()->subHours(2))->first();

    if ($contactMessage) {
      return response()->json(['message' => 'You have already sent a message recently. We will reply as soon as possible, thank you!'], 422);
    }

    $save = array_merge($validated, [
      'path'     => $request->path(),
      'url'      => $request->url(),
      'full_url' => $request->fullUrl(),
    ]);
    
    StoreMessageJob::dispatch($save);
    
    return response()->json(['message' => 'Thank you for your message, it has been sent. We will reply as soon as possible, thank you!'], 200);
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
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $id)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(string $id)
  {
    //
  }

  private function _set_validator(Request $request): validationValidator
  {
    $rules = [
      'name'          => 'required|min:3',
      'email'         => 'required|email',
      'subject'       => 'required|min:5',
      'message'       => 'required|min:5|max:2000',
      'captcha_token' => 'required|string',
      'ip_address'    => 'nullable|string',
      'user_agent'    => 'nullable|string',
    ];

    $messages = [
      'name.required'          => 'Please enter your name',
      'name.min'               => 'Name must be at least 3 characters',
      'email.required'         => 'Please enter your email',
      'email.email'            => 'Email is invalid',
      'subject.required'       => 'Please enter your subject',
      'subject.min'            => 'Subject must be at least 5 characters',
      'message.required'       => 'Please enter your message',
      'message.min'            => 'Message must be at least 5 characters',
      'message.max'            => 'Message must be at most 400 characters',
      'captcha_token.required' => 'Please complete the captcha',
    ];

    $validator = Validator::make($request->all(), $rules, $messages);
    return $validator;
  }
}
