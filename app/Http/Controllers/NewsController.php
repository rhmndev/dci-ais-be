<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Imports\NewsImport;
use App\News;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Image;
use Excel;

class NewsController extends Controller
{
    //
    public function index(Request $request)
    {
        
        $request->validate([
            'columns' => 'required',
            'perpage' => 'required|numeric',
            'page' => 'required|numeric',
            'sort' => 'required|string',
            'order' => 'string',
        ]);

        $keyword = ($request->keyword != null) ? $request->keyword : '';
        $order = ($request->order != null) ? $request->order : 'ascend';

        try {
    
            $News = new News;
            $data = array();

            $resultAlls = $News->getAllData($keyword, $request->columns, $request->sort, $order);
            $results = $News->getData($keyword, $request->columns, $request->perpage, $request->page, $request->sort, $order);

            return response()->json([
                'type' => 'success',
                'data' => $results,
                'total' => count($resultAlls),
            ], 200);

        } catch (\Exception $e) {
    
            return response()->json([
    
                'type' => 'failed',
                'message' => 'Err: '.$e.'.',
                'data' => NULL,
    
            ], 400);

        }
    }
    
    public function show(Request $request, $id)
    {
        $News = News::findOrFail($id);

        return response()->json([
            'type' => 'success',
            'data' =>  $News
        ]);
    }
    
    public function store(Request $request)
    {
        
        $request->validate([
            'title' => 'required|string',
            'text' => 'required|string',
            'photo' => $request->photo != null && $request->hasFile('photo') ? 'sometimes|image|mimes:jpeg,jpg,png|max:2048' : '',
        ]);

        try {

            $News = News::firstOrNew(['title' => $request->title]);

            $News->title = $request->title;
            $News->text = $request->text;
            
            if ($request->photo != null && $request->hasFile('photo')) {
        
                if (Storage::disk('public')->exists('/images/news/'.$News->photo)) {
                    Storage::disk('public')->delete('/images/news/'.$News->photo);
                }

                $image      = $request->file('photo');
                $fileName   = rand().'.' . $image->getClientOriginalExtension();
    
                $img = Image::make($image->getRealPath());
                $img->resize(250, 250, function ($constraint) {
                    $constraint->aspectRatio();                 
                });
    
                $img->stream(); // <-- Key point
                
                Storage::disk('public')->put('/images/news'.'/'.$fileName, $img, 'public');

                $News->photo = $fileName;
                
            }

            $News->created_by = auth()->user()->username;
            $News->updated_by = auth()->user()->username;

            $News->save();

            return response()->json([
                'type' => 'success',
                'message' => 'Data saved successfully!',
                'data' => NULL,
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
    
                'type' => 'failed',
                'message' => 'Err: '.$e.'.',
                'data' => NULL,
    
            ], 400);

        }
    }
    
    public function destroy(Request $request, $id)
    {
        $News = News::find($id);
        
        if (Storage::disk('public')->exists('/images/news/'.$News->photo)) {
            Storage::disk('public')->delete('/images/news/'.$News->photo);
        }

        $News->delete();

        return response()->json([
            'type' => 'success',
            'message' => 'Data deleted successfully'
        ], 200);
    }
}
