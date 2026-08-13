<?php
namespace App\Http\Controllers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
class CustomerAccountController extends Controller
{
    public function index(Request $request): View
    {
        $customer=$request->attributes->get('customer');
        $orders=DB::table('checkout_requests')->where('customer_id',$customer->id)->orWhere('email',$customer->email)->latest()->get()->map(function($order){$order->items=json_decode($order->cart ?: '[]',true) ?: [];return $order;});
        return view('customer-account',['customer'=>$customer,'orders'=>$orders,'addresses'=>DB::table('customer_addresses')->where('customer_id',$customer->id)->orderByDesc('is_default')->latest()->get(),'countries'=>config('naturmarkt.shipping.countries',[])]);
    }
    public function updateProfile(Request $request): RedirectResponse
    {
        $customer=$request->attributes->get('customer');
        $data=$request->validate(['name'=>['required','string','max:120'],'email'=>['required','email','max:255',Rule::unique('customers','email')->ignore($customer->id)]]);
        $email=mb_strtolower($data['email']);
        if(DB::table('owner_users')->where('email',$email)->exists()) return back()->withErrors(['email'=>'Diese E-Mail-Adresse gehört zum Besitzerkonto.']);
        $changed=!hash_equals($customer->email,$email);
        DB::table('customers')->where('id',$customer->id)->update(['name'=>$data['name'],'email'=>$email,'email_verified_at'=>$changed?null:$customer->email_verified_at,'updated_at'=>now()]);
        return back()->with('success',$changed?'Daten gespeichert. Bitte bestätige deine neue E-Mail-Adresse über „Bestätigung erneut senden“.':'Persönliche Daten wurden gespeichert.');
    }
    public function updatePassword(Request $request): RedirectResponse
    {
        $customer=$request->attributes->get('customer');
        $data=$request->validate(['current_password'=>['required','string'],'password'=>['required','confirmed',Password::min(12)->mixedCase()->numbers()]]);
        if(!Hash::check($data['current_password'],$customer->password)) return back()->withErrors(['current_password'=>'Das aktuelle Passwort ist nicht korrekt.']);
        DB::table('customers')->where('id',$customer->id)->update(['password'=>Hash::make($data['password']),'updated_at'=>now()]);
        $request->session()->regenerate();
        return back()->with('success','Dein Passwort wurde sicher geändert.');
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
    public function updateAddress(Request $request,int $address): RedirectResponse
    {
        $customer=$request->attributes->get('customer');
        $data=$request->validate(['label'=>['required','string','max:60'],'name'=>['required','string','max:120'],'phone'=>['nullable','string','max:80'],'street'=>['required','string','max:255'],'postal_code'=>['required','string','max:20'],'city'=>['required','string','max:120'],'country_code'=>['required','in:DE,AT,NL,LU'],'is_default'=>['nullable','boolean']]);
        if($request->boolean('is_default')) DB::table('customer_addresses')->where('customer_id',$customer->id)->update(['is_default'=>false]);
        DB::table('customer_addresses')->where('id',$address)->where('customer_id',$customer->id)->update([...$data,'is_default'=>$request->boolean('is_default'),'updated_at'=>now()]);
        return back()->with('success','Adresse wurde aktualisiert.');
    }
    public function deleteAccount(Request $request): RedirectResponse
    {
        $customer=$request->attributes->get('customer');
        $data=$request->validate(['password'=>['required','string'],'confirmation'=>['required','in:KONTO LÖSCHEN']]);
        if(!Hash::check($data['password'],$customer->password)) return back()->withErrors(['delete_account'=>'Das Passwort ist nicht korrekt.']);
        DB::transaction(function()use($customer){DB::table('checkout_requests')->where('customer_id',$customer->id)->update(['customer_id'=>null]);DB::table('customers')->where('id',$customer->id)->delete();});
        $request->session()->invalidate();$request->session()->regenerateToken();
        return redirect()->route('home')->with('success','Dein Kundenkonto wurde gelöscht. Gesetzlich erforderliche Bestelldaten bleiben getrennt davon erhalten.');
    }
}
