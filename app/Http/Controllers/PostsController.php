<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Discussion;
use App\User;
use App\Markdown\Markdown;
use Auth;

class PostsController extends Controller
{
    protected $markdown;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(Markdown $markdown)
    {
      $this->middleware('auth',['only'=>['create','store','edit','update']]);
      $this->markdown=$markdown;
    }
    public function index()
    {
        $discussions=Discussion::latest()->get();
        return view('forum.index',compact('discussions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('forum.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

      $this->validate($request,[
        'title'=>'required',
        'body'=>'required',
      ]);
      $data=[
        'user_id'=>\Auth::user()->id,
        'last_user_id'=>\Auth::user()->id,
      ];

      $discussion=Discussion::create(array_merge($request->all(),$data));
      session()->flash('success','发表成功');
      return redirect()->action('PostsController@show',['id'=>$discussion->id]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $discussion=Discussion::findOrFail($id);
        $html=$this->markdown->markdown($discussion->body);
        return view('forum.show',compact('discussion','html'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $discussion=Discussion::findOrFail($id);
        if(Auth::user()->id!==$discussion->user_id)
        {
          return redirect('/');
        }
        return view('forum.edit',compact('discussion'));
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
        $this->validate($request,[
          'title'=>'required',
          'body'=>'required',
        ]);

        $discussion=Discussion::findOrFail($id);
        $discussion->update($request->all());
        return redirect()->action('PostsController@show',['id'=>$discussion->id]);
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
