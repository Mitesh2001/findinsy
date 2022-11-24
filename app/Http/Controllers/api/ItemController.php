<?php

namespace App\Http\Controllers\api;

use App\Models\Item;
use App\Models\Box;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        try {

            $items = Box::find($request->box_id)->items;
            return response()->json(['items' => $items, 'message' => 'Item fetched successfully !', 'success' => true], 200);

        } catch (\Throwable $th) {

            $errors['success'] = false;
            $errors['message'] = "Something went wrong !";
            return response()->json($errors, 401);
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try{

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'box_id' => 'required',
                'category_id' => 'exists:categories,id',
                'icon' => 'mimetypes:image/*'
            ],[
                'category_id.exists' => "Category doesn't exists !"
            ]);

            if($validator->fails()){
                $errorString = implode(",", $validator->messages()->all());
                return response()->json([
                    'success' => false,
                    'message' => $errorString
                ]);
            }

            $icon = "";

            if ($request->hasfile('icon')) {

                $imageFile = $request->file('icon');
                $name = $imageFile->getClientOriginalName();
                $imageFile->move(public_path().'/item_icons/',$name);
                $icon = '/item_icons/'.$name;
            }

            $item = Item::create([
                'name' => $request->name,
                'description' => $request->description,
                'user_id' => auth()->id(),
                'category_id' => $request->category_id,
                'box_id' => $request->box_id,
                'icon' => $icon
            ]);

            return response()->json(['item' => $item, 'message' => 'Item Added to Box !', 'success' => true], 200);


        } catch (\Throwable $th) {

            $errors['success'] = false;
            $errors['message'] = "Something went wrong !";
            return response()->json($errors, 401);

        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function show(Item $item)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function edit(Item $item)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Item $item)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function destroy(Item $item)
    {
        //
    }

    public function renameItem(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'new_name' => 'required|string|max:255',
                'box_id' => 'required',
                'item_id' => 'required',
            ]);

            if($validator->fails()){
                $errorString = implode(",", $validator->messages()->all());
                return response()->json([
                    'success' => false,
                    'message' => $errorString
                ]);
            }

            if (Box::find($request->box_id)->items->contains($request->item_id)) {
                $item = Item::find($request->item_id);
                $item->update(['name' => $request->new_name]);
                return response()->json(['item' => $item, 'message' => "Item renamed successfully !", 'success' => true], 200);
            } else {
                return response()->json(['message' => "Item doesn't exist or not belongs to Box !", 'success' => false], 200);
            }

        } catch (\Throwable $th) {

            $errors['success'] = false;
            $errors['message'] = "Something went wrong !";
            return response()->json($errors, 401);

        }
    }

}
