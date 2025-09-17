<?php

namespace App\Controllers;

class ErrorController extends Home
{
    public function forbidden()
    {
        return view('errors/html/error_403');
    }

    public function unavailable()
    {
		return view('errors/html/error_503');
	}
}