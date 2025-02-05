<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/pokemon-sleep');
});
Route::view('/pokemon-sleep', 'pokemon-sleep.pokemon-sleep');
Route::view('/mtg-lister', 'mtg-lister.mtg-lister');
