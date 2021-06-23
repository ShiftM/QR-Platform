<?php

namespace App\Http\Controllers\V1_1_0;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\FirstEmail;

class EmailController extends Controller
{
    public function sendEmail() {

        $to_email = "emmanuelluis.ramirez@gmail.com";

        Mail::to($to_email)->send(new FirstEmail);

        if(Mail::failures() != 0) {
            return "<p> Success! Your E-mail has been sent.</p>";
        }

        else {
            return "<p> Failed! Your E-mail has not sent.</p>";
        }
    }
}