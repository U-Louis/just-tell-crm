<?php

use App\Http\Controllers\Crm\CreateContactController;
use App\Http\Controllers\Crm\QualifyContactController;
use App\Http\Controllers\Crm\SelectActingUserController;
use App\Http\Controllers\Crm\SendEstimateController;
use App\Http\Controllers\Crm\ShowContactController;
use App\Http\Controllers\Crm\ShowKanbanController;
use Illuminate\Support\Facades\Route;

Route::get('/', ShowKanbanController::class)->name('home');
Route::post('/acting-user', SelectActingUserController::class)->name('acting-user.store');
Route::post('/contacts', CreateContactController::class)->name('contacts.store');
Route::get('/contacts/{contact}', ShowContactController::class)->name('contacts.show');
Route::post('/contacts/{contact}/qualify', QualifyContactController::class)->name('contacts.qualify');
Route::post('/contacts/{contact}/estimates', SendEstimateController::class)->name('contacts.estimates.store');
