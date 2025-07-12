<?php

namespace App\Http\Controllers;

use App\Http\Resources\PaymentAccountResource;
use App\Models\PaymentAccount;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PaymentAccountController extends Controller
{
  use AuthorizesRequests;
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $this->authorize('viewAny', PaymentAccount::class);

    $data = PaymentAccount::orderBy('updated_at', 'desc')
      ->paginate(10);

    return PaymentAccountResource::collection($data);
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
    //
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
}
