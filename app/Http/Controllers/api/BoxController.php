<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;
use App\Models\Box;

class BoxController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {

            $boxes = auth()->user()->boxes->where('category_id',3);

            return response()->json(['boxes' => $boxes, 'message' => 'All Boxes has been fetched successfully !', 'success' => true], 200);

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

        try {

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:boxes',
                'category_id' => 'exists:categories,id'
            ],[
                'category_id.exists' => "Category doesn't exists !"
            ]);

            if($validator->fails()){

                $errorString = implode(",", $validator->messages()->all());
                return response()->json([
                    'message' => $errorString,
                    'success' => false
                ]);

            }

            $category = Category::find($request->get('category_id'));

            $box = $category->boxes()->create([
                'name' => $request->get('name'),
                'description' => $request->get('description')
            ]);

            auth()->user()->boxes()->attach($box);

            return response()->json(['box' => $box, 'message' => 'New Box has been created successfully !', 'success' => true], 200);

        } catch (\Throwable $th) {

            DB::rollBack();
            $errors['success'] = false;
            $errors['message'] = "Something went wrong !";
            if ($request->debug_mode == 'ON') {
                $errors['debug'] = $th->getMessage();
            }
            return response()->json($errors, 401);

        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        try {

            $validator = Validator::make($request->all(), [
                'name' => "required|string|max:255|unique:boxes,name,$id",
                'category_id' => 'exists:categories,id'
            ],[
                'category_id.exists' => "Category doesn't exists !"
            ]);

            if($validator->fails()){

                $errorString = implode(",", $validator->messages()->all());
                return response()->json([
                    'message' => $errorString,
                    'success' => false
                ]);

            }

            $box = Box::find($id);

            $box->update([
                'name' => $request->get('name'),
                'description' => $request->get('description'),
                'category_id' => $request->get('category_id')
            ]);

            return response()->json(['box' => $box, 'message' => 'Box has been updated successfully !', 'success' => true], 200);

        } catch (\Throwable $th) {

            $errors['success'] = false;
            $errors['message'] = "Something went wrong !";
            return response()->json($errors, 401);

        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {

            Box::find($id)->delete();
            return response()->json(['message' => 'Box has been deleted successfully !', 'success' => true], 200);

        } catch (\Throwable $th) {

            $errors['success'] = false;
            $errors['message'] = "Something went wrong !";
            return response()->json($errors, 401);

        }
    }
}
