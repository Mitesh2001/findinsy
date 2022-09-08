<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
                'name' => 'required|string|max:255|unique:categories',
                'icon' => 'mimetypes:image/*'
            ]);

            if($validator->fails()){

                $errorString = implode(",", $validator->messages()->all());
                return response()->json([
                    'message' => $errorString,
                    'success' => false
                ]);

            }

            $category_icon = "";

            if ($request->hasfile('icon')) {

                $imageFile = $request->file('icon');
                $name = $imageFile->getClientOriginalName();
                $imageFile->move(public_path().'/category_icons/',$name);

                $category_icon = '/category_icons/'.$name ;
            }

            $category = Category::create([
                'name' => $request->name,
                'icon' => $category_icon,
                'user_id' => auth()->id()
            ]);

            return response()->json(['category' => $category, 'message' => 'New category has been created successfully !', 'success' => false], 200);

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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
