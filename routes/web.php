<?php

use Illuminate\Support\Facades\Route;

Route::get("/", fn() => redirect("/admin"));

// Route::get("/", function () {
//     return view('welcome');
// });