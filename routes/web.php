<?php

use App\Http\Controllers\Crm\ContactController;
use App\Http\Controllers\Crm\EstimateController;
use App\Http\Controllers\Crm\KanbanController;
use App\Http\Controllers\Crm\ProductController;
use App\Http\Controllers\Crm\UserController;
use App\Http\Controllers\Crm\VoiceController;
use App\Http\Controllers\Crm\VoiceToolController;
use App\Http\Middleware\VoiceToken;
use Illuminate\Support\Facades\Route;

Route::post('/voice', [VoiceController::class, 'store'])->name('voice.store');
Route::middleware(VoiceToken::class)->group(function () {
    Route::get('/voice/catalog', [VoiceToolController::class, 'catalog'])->name('voice.catalog');
    Route::post('/voice/tools/{tool}', [VoiceToolController::class, 'call'])->name('voice.tools');
});

Route::get('/', [KanbanController::class, 'index'])->name('home');
Route::post('/acting-user', [UserController::class, 'select'])->name('acting-user.store');

Route::get('/users/export.csv', [UserController::class, 'csv'])->name('users.csv');
Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
Route::post('/users', [UserController::class, 'store'])->name('users.store');
Route::get('/users/{user}.pdf', [UserController::class, 'pdf'])->whereNumber('user')->name('users.pdf');
Route::get('/users/{user}', [UserController::class, 'show'])->whereNumber('user')->name('users.show');
Route::put('/users/{user}', [UserController::class, 'update'])->whereNumber('user')->name('users.update');
Route::delete('/users/{user}', [UserController::class, 'destroy'])->whereNumber('user')->name('users.destroy');

Route::get('/contacts/export.csv', [ContactController::class, 'csv'])->name('contacts.csv');
Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
Route::get('/contacts/create', [ContactController::class, 'create'])->name('contacts.create');
Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');
Route::get('/contacts/{contact}.pdf', [ContactController::class, 'pdf'])->whereNumber('contact')->name('contacts.pdf');
Route::get('/contacts/{contact}', [ContactController::class, 'show'])->whereNumber('contact')->name('contacts.show');
Route::put('/contacts/{contact}', [ContactController::class, 'update'])->whereNumber('contact')->name('contacts.update');
Route::delete('/contacts/{contact}', [ContactController::class, 'destroy'])->whereNumber('contact')->name('contacts.destroy');
Route::post('/contacts/{contact}/qualify', [ContactController::class, 'qualify'])->whereNumber('contact')->name('contacts.qualify');
Route::post('/contacts/{contact}/estimates', [EstimateController::class, 'store'])->whereNumber('contact')->name('contacts.estimates.store');

Route::get('/products/export.csv', [ProductController::class, 'csv'])->name('products.csv');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::post('/products', [ProductController::class, 'store'])->name('products.store');
Route::get('/products/{product}.pdf', [ProductController::class, 'pdf'])->whereNumber('product')->name('products.pdf');
Route::get('/products/{product}', [ProductController::class, 'show'])->whereNumber('product')->name('products.show');
Route::put('/products/{product}', [ProductController::class, 'update'])->whereNumber('product')->name('products.update');
Route::delete('/products/{product}', [ProductController::class, 'destroy'])->whereNumber('product')->name('products.destroy');

Route::get('/estimates/export.csv', [EstimateController::class, 'csv'])->name('estimates.csv');
Route::get('/estimates', [EstimateController::class, 'index'])->name('estimates.index');
Route::get('/estimates/create', [EstimateController::class, 'create'])->name('estimates.create');
Route::get('/estimates/{estimate}.pdf', [EstimateController::class, 'pdf'])->whereNumber('estimate')->name('estimates.pdf');
Route::get('/estimates/{estimate}', [EstimateController::class, 'show'])->whereNumber('estimate')->name('estimates.show');
Route::put('/estimates/{estimate}', [EstimateController::class, 'update'])->whereNumber('estimate')->name('estimates.update');
Route::delete('/estimates/{estimate}', [EstimateController::class, 'destroy'])->whereNumber('estimate')->name('estimates.destroy');
