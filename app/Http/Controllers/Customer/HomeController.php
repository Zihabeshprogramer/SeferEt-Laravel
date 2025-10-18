<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function GetMedia($media){
        return response()->file(public_path().'/assets/images/'.$media);
    }
}
