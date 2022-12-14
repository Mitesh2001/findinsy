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
    public function index(Request $request)
    {
        $data = [];
        foreach (auth()->user()->boxes as $key => $box) {
            if ($box->category_id == $request->category_id) {
                $data[] = $box;
            }
        }
        try {

            return response()->json(['boxes' => $data, 'message' => 'All Boxes has been fetched successfully !', 'success' => true], 200);

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
                'name' => 'required|string|max:255',
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

            auth()->user()->boxes->where('id',$id)->first()->delete();
            return response()->json(['message' => 'Box has been deleted successfully !', 'success' => true], 200);

        } catch (\Throwable $th) {

            $errors['success'] = false;
            $errors['message'] = "Something went wrong !";
            return response()->json($errors, 401);

        }
    }

    public function boxMove(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'box_id' => "required",
                'category_id' => 'required|exists:categories,id'
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

            $box = Box::find($request->box_id);
            $box->update([
                'category_id' => $request->category_id
            ]);

            return response()->json(['box' => $box,'message' => 'Box has been moved to new Category !', 'success' => true], 200);

        } catch (\Throwable $th) {

            $errors['success'] = false;
            $errors['message'] = "Something went wrong !";
            return response()->json($errors, 401);

        }
    }

    public function renameBox(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'new_name' => "required|string|max:255|unique:boxes,name,$request->box_id",
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

            $category = Category::find($request->category_id);

            if ($category->boxes->contains($request->box_id)) {
                $box = Box::find($request->box_id);
                $box->update(['name' => $request->new_name]);

            } else {

                return response()->json(['message' => "Box doesn't exist or not belongs to $category->name category !", 'success' => false], 200);

            }

            return response()->json(['box' => $box,'message' => 'Box has been renamed successfully !', 'success' => true], 200);


        } catch (\Throwable $th) {

            $errors['success'] = false;
            $errors['message'] = "Something went wrong !";
            return response()->json($errors, 401);

        }
    }
}
