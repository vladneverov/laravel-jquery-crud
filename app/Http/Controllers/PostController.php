<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use DataTables;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ( $request->ajax() )
        {
            return DataTables::make( Post::latest()->get() )
                    ->addColumn('action', 'posts.action')
                    ->rawColumns(['action'])
                    ->editColumn('created_at', function ($row)
                    {
                        return e( $row->updated_at->format('H:i d/m/Y') );
                    })
                    ->make(true);
        }

        return view('posts.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:30',
            'description' => 'required|max:100',
        ]);

        Post::updateOrCreate(['id' => $request->id],
             $request->only( ['title', 'description'] ));

        return response()
               ->json(['success' => 'Запись успешно добавлена или отредактирована.']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return response()->json( Post::find($id) );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Post::find($id)->delete();
     
        return response()->json(['success' => 'Запись удалена.']);
    }
}
