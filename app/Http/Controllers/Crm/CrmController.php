<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CrmController extends Controller
{
    public function index(){
        return view('crm.index');
    }
}
