<?php

namespace App\Http\Controllers;

use App\PartComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PartComponentController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'columns' => 'required',
            'perpage' => 'required|numeric',
            'page' => 'required|numeric',
            'sort' => 'required|string',
            'order' => 'string',
            'groupByCustomer' => 'boolean'
        ]);

        $keyword = ($request->keyword != null) ? $request->keyword : '';
        $order = ($request->order != null) ? $request->order : 'ascend';
        $groupByCustomer = $request->groupByCustomer ?? false;

        try {

            $PartComponent = new PartComponent();

            if ($groupByCustomer) {
                $results = $PartComponent->getDataGroupedByCustomer(
                    $keyword,
                    $request->columns,
                    $request->perpage,
                    $request->page,
                    $request->sort,
                    $order
                );

                $total = 0;
                foreach ($results as $customerGroup) {
                    $total += count($customerGroup);
                }
            } else {
                $resultAlls = $PartComponent->getAllData($keyword, $request->columns, $request->sort, $order);
                $results = $PartComponent->getData($keyword, $request->columns, $request->perpage, $request->page, $request->sort, $order);
                $total = count($resultAlls);
            }

            return response()->json([
                'type' => 'success',
                'data' => $results,
                'total' => $total
            ], 200);
        } catch (\Exception $e) {

            return response()->json([

                'type' => 'failed',
                'message' => 'Err: ' . $e . '.',
                'data' => NULL,
            ], 400);
        }
    }

    public function show(Request $request, $id)
    {
        $PartComponent = PartComponent::findOrFail($id);

        return response()->json([
            'type' => 'success',
            'data' =>  $PartComponent
        ]);
    }

    public function store(Request $request)
    {

        $request->validate([
            'customer_id' => 'required',
            'name' => 'required',
            'number' => 'required|string',
            'photo' => $request->photo != null && $request->hasFile('photo') ? 'sometimes|image|mimes:jpeg,jpg,png|max:2048' : '',
            'description' => 'string'
        ]);

        try {

            $PartComponent = PartComponent::firstOrNew(['code' => $request->code]);

            $PartComponent->customer_id = $request->customer_id;
            $PartComponent->name = $request->name;
            $PartComponent->description = $request->description;
            $PartComponent->number = $request->number;

            if ($request->photo != null && $request->hasFile('photo')) {

                if (Storage::disk('public')->exists('/images/part_component/' . $PartComponent->photo)) {
                    Storage::disk('public')->delete('/images/part_component/' . $PartComponent->photo);
                }

                $image      = $request->file('photo');
                $fileName   = 'pc_' . $PartComponent->customer_id . '_' . $PartComponent->name . '.' . $image->getClientOriginalExtension();

                $img = Image::make($image->getRealPath());
                $img->resize(250, 250, function ($constraint) {
                    $constraint->aspectRatio();
                });

                $img->stream(); // <-- Key point

                Storage::disk('public')->put('/images/part_component' . '/' . $fileName, $img, 'public');

                $PartComponent->photo = $fileName;
            }

            $PartComponent->created_by = auth()->user()->username;
            $PartComponent->updated_by = auth()->user()->username;

            $PartComponent->save();

            return response()->json([
                'type' => 'success',
                'message' => 'Data saved successfully!',
                'data' => NULL,
            ], 200);
        } catch (\Exception $e) {

            return response()->json([

                'type' => 'failed',
                'message' => 'Err: ' . $e . '.',
                'data' => NULL,

            ], 400);
        }
    }

    public function destroy($id)
    {
        $PartComponent = PartComponent::find($id);

        if (Storage::disk('public')->exists('/images/part_component/' . $PartComponent->photo)) {
            Storage::disk('public')->delete('/images/part_component/' . $PartComponent->photo);
        }

        $PartComponent->delete();

        return response()->json([
            'type' => 'success',
            'message' => 'Data deleted successfully'
        ], 200);
    }

    private function stringtoupper($string)
    {
        if ($string != '') {
            $string = strtolower($string);
            $string = strtoupper($string);
        }
        return $string;
    }

    public function list(Request $request)
    {
        $PartComponent = PartComponent::when($request->keyword, function ($query) use ($request) {
            if (!empty($request->keyword)) {
                $query->where('name', 'like', '%' . $request->keyword . '%');
            }
        })->take(10)->get();

        return response()->json([
            'type' => 'success',
            'data' => $PartComponent
        ], 200);
    }
}
