<?php

namespace App\Http\Controllers;

use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Illuminate\Http\Request;

class QrCodeController extends Controller
{
    public function generateQrCode($id)
    {
        $qrCode = new QrCode($id);
        $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::High);

        return response($qrCode->writeString())
            ->header('Content-Type', 'image/png');
    }
} 