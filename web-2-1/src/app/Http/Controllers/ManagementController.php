<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\Representative;
use App\Models\Shop;
use App\Models\Reserve;
use App\Http\Requests\ShopRequest;
use App\Mail\InformMail;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Support\Facades\Storage;



class ManagementController extends Controller
{
    public function managementIndex(Request $request){

    $shops=Representative::with('shop')->where('user_id',Auth::user()->id)->get();
    return view('/management',compact('shops'));
    }

    public function shopUpdateIndex(Request $request){

    $shopUpdate=Shop::find($request->id);
    $shops=Representative::with('shop')->where('user_id',Auth::user()->id)->get();
    return view('/management',compact('shopUpdate','shops'));
    }

    public function shopReserve(Request $request){
    $reserves=Reserve::with('user','shop')->withTrashed()->where('shop_id',$request->id)->StartDateSearch($request->startDate)->EndDateSearch($request->endDate)->get();
    $shops=Representative::with('shop')->where('user_id',Auth::user()->id)->get();
    return view('/management',compact('reserves','shops'));
    }

    public function shopCreate(ShopRequest $request){
    $image_name=$request->file('image')->getClientOriginalName();
    $path=Storage::disk('s3')->putFile('sample', $request->file('image'));
    $shop=['name'=>$request->name,'area'=>$request->area,'category'=>$request->category,'overview'=>$request->overview,'image_name' => $image_name,'path' => Storage::disk('s3')->url($path)];
    Shop::create($shop);
    $shopId=Shop::latest('id')->first();
    Representative::create(['shop_id' => $shopId->id,'user_id' => Auth::user()->id]);
    return redirect('/managementIndex');
    }

    public function shopUpdate(Request $request){

    if($request->file('image')!==null){
        $image_name=$request->file('image')->getClientOriginalName();
        $path=Storage::disk('s3')->putFile('sample', $request->file('image'));
        $shop=['image_name' => $image_name,'path' => Storage::disk('s3')->url($path)];
        }

    if($request->name!==null){
    $shop['name']=$request->name;}

    if($request->area!==null){
    $shop['area']=$request->area;}

    if($request->category!==null){
    $shop['category']=$request->category;}

    if($request->overview!==null){
    $shop +=array('overview'=>$request->overview);}

    Shop::find($request->id)->update($shop);
    return redirect('/managementIndex');
    }

    public function informMail(Request $request,Mailer $mailer){

    $mailer->to($request->email)->send(new InformMail($request->name,$request->date,$request->time,$request->shop,$request->hc,$request->recommendation));

    $shops=Representative::with('shop')->where('user_id',Auth::user()->id)->get();
    return view('/management',compact('shops'));
    }


}
