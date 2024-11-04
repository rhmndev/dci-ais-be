<?php

namespace App\Http\Controllers;

use App\LineNumber;
use Illuminate\Http\Request;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;

class LineNumberController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'nullable|string',
            'name' => 'required|string',
        ]);

        $LineNumber = LineNumber::firstOrNew(['code' => $request->code]);

        if (!$request->has('code')) {
            $LineNumber->code = $this->stringtoupper($this->generateLineNumber());
        }

        $LineNumber->name = $this->stringtoupper($request->name);
        if (!$LineNumber->qr_path) {
            $LineNumber->qr_path = $this->generateAndStoreQRCode($LineNumber->code);
        }
    }

    private function generateLineNumber()
    {
        $lastLineNumber = LineNumber::orderBy('created_at', 'desc')->first();

        if ($lastLineNumber) {
            $lastNumber = (int)substr($lastLineNumber->code, -3);
            $nextNumber = $lastNumber + 1;
            return 'LN' . sprintf('%03d', $nextNumber);
        } else {
            return 'LN001';
        }
    }

    private function stringtoupper($string)
    {
        if ($string != '') {
            $string = strtolower($string);
            $string = strtoupper($string);
        }
        return $string;
    }

    public function show(Request $request, $id)
    {
        $LineNumber = LineNumber::findOrFail($id);

        return response()->json([
            'type' => 'success',
            'data' =>  $LineNumber
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'code' => 'nullable|string',
            'name' => 'required|string',
        ]);

        $LineNumber = LineNumber::findOrFail($id);

        $LineNumber->name = $this->stringtoupper($request->name);
        $LineNumber->save();

        return response()->json([
            'type' => 'success',
            'message' => 'Data updated successfully!'
        ], 201);
    }

    public function destroy($id)
    {
        $LineNumber = LineNumber::find($id);

        if (Storage::disk('public')->exists('/qrcodes/line_number/' . $LineNumber->qr_path)) {
            Storage::disk('public')->delete('/qrcodes/line_number/' . $LineNumber->qr_path);
        }
    }

    private function generateAndStoreQRCode($code)
    {
        $qrCode = QrCode::create($code);
        $qrCode->setSize(300);

        $writer = new PngWriter();
        $qrCodeData = $writer->write($qrCode);

        $fileName = 'qrcodes/line_number_' . $code . '.png';

        Storage::disk('public')->put($fileName, $qrCodeData->getString());

        return $fileName;
    }

    public function list(Request $request)
    {
        $lineNumbers = LineNumber::when($request->keyword, function ($query) use ($request) {
            if (!empty($request->keyword)) {
                $query->where('name', 'like', '%' . $request->keyword . '%');
            }
        })->take(10)
            ->get();

        return response()->json([
            'type' => 'success',
            'data' => $lineNumbers
        ], 200);
    }
}
