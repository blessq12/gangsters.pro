<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class MainController extends Controller
{
    public function __construct()
    {
    }

    public function index()
    {
        return view('front.index');
    }

    public function about()
    {
        return view('front.about');
    }
    public function vacancy()
    {
    }
    public function contact()
    {
    }
    public function sendXml()
    {
        $client = new \SoapClient('http://smev3-n0.test.gosuslugi.ru:7500/smev/v1.2/ws?wsdl');
        $response = $client->sendRequest([
            'SenderProvidedRequestData' => [
                'MessageID' => '348653456dsf535w',
                'MessagePrimaryContent' => [
                    'any' => ''
                ]
            ]
        ]);
        dd($response);
    }
}
