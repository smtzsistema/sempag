<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CnpjController;

Route::get('/cnpj', [CnpjController::class, 'show']);
