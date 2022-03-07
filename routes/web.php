<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportData;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::match(['get', 'post'], '/import/{sort?}/{number?}', [ImportData::class, 'store'])->name('import_storage_data');

Route::get('/', function () {
    return view('import');
});
