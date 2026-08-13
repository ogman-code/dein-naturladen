<?php
namespace App\Http\Controllers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
class CustomerAccountController extends Controller
{
    public function index(Request $request): View
    {
        $customer=$request->attributes->get('customer');
        return view('customer-account',['customer'=>$customer,'orders'=>DB::table('checkout_requests')->where('customer_id',$customer->id)->orWhere('email',$customer->email)->latest()->get(),'addresses'=>DB::table('customer_addresses')->where('customer_id',$customer->id)->orderByDesc('is_default')->latest()->get(),'countries'=>config('naturmarkt.shipping.countries',[])]);
    }
    public function storeAddress(Request $request): RedirectResponse
    {
        $customer=$request->attributes->get('customer');
        $data=$request->validate(['label'=>['required','string','max:60'],'name'=>['required','string','max:120'],'phone'=>['nullable','string','max:80'],'street'=>['required','string','max:255'],'postal_code'=>['required','string','max:20'],'city'=>['required','string','max:120'],'country_code'=>['required','in:DE,AT,NL,LU'],'is_default'=>['nullable','boolean']]);
        if($request->boolean('is_default')) DB::table('customer_addresses')->where('customer_id',$customer->id)->update(['is_default'=>false]);
        DB::table('customer_addresses')->insert([...$data,'customer_id'=>$customer->id,'is_default'=>$request->boolean('is_default'),'created_at'=>now(),'updated_at'=>now()]);
        return back()->with('success','Adresse wurde gespeichert.');
    }
    public function deleteAddress(Request $request,int $address): RedirectResponse
    {
        DB::table('customer_addresses')->where('id',$address)->where('customer_id',$request->attributes->get('customer')->id)->delete();
        return back()->with('success','Adresse wurde entfernt.');
    }
}
