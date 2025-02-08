<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/mtg-lister');
});
Route::view('/pokemon-sleep', 'pokemon-sleep.pokemon-sleep');
Route::view('/mtg-lister', 'mtg-lister.mtg-lister');
