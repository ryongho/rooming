<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function regist(Request $request)
    {
        //dd($request);
        $return = new \stdClass;

        $return->status = "500";
        $return->msg = "관리자에게 문의";
        $return->data = $request->user_id;

        /* 중복 체크 - start*/
        $id_cnt = User::where('user_id',$request->user_id)->count();
        $email_cnt = User::where('email',$request->email)->count();
        $phone_cnt = User::where('phone',$request->phone)->count();

        if($id_cnt){
            $return->status = "601";
            $return->msg = "사용중인 아이디";
            $return->data = $request->user_id;
        }else if($email_cnt){
            $return->status = "602";
            $return->msg = "사용중인 이메일";
            $return->data = $request->email;
        }else if ($phone_cnt){
            $return->status = "603";
            $return->msg = "사용중인 폰 번호";
            $return->data = $request->phone;
        /* 중복 체크 - end*/
        }else{
            $result = User::insert([
                'name'=> $request->name ,
                'email' => $request->email, 
                'password' => $request->password, 
                'user_id' => $request->user_id,
                'phone' => $request->phone, 
                'user_type' => $request->user_type,
                'created_at' => Carbon::now(),
                'password' => Hash::make($request->password)
                
            ]);

            if($result){
                $return->status = "200";
                $return->msg = "success";
                $return->data = $request->user_id;
            }
        }
        

        echo(json_encode($return));

        //return view('user.profile', ['user' => User::findOrFail($id)]);
    }

    public function login(Request $request){
        $user = User::where('email' , $request->email)->first();

        $return = new \stdClass;

        if(!$user){
            $return->status = "501";
            $return->msg = "존재하지 않는 아이디 입니다.";
            $return->email = $request->email;
        }else if (Hash::check($request->password, $user->password)) {
            //echo("로그인 확인");
            Auth::loginUsingId($user->id);
            $login_user = Auth::user();

            $token = $login_user->createToken('user');

            $return->status = "200";
            $return->msg = "성공";
            $return->token = $token->plainTextToken;    
            
            //dd($token->plainTextToken);    
        }else{
            $return->status = "500";
            $return->msg = "아이디 또는 패스워드가 일치하지 않습니다.";
            $return->email = $request->email;
        }

        echo(json_encode($return));
    }

    public function logout(Request $request){
        $user = Auth::user(); 
        Auth::logout();
    }

    public function login_check(Request $request){
        $user = Auth::user(); 
        dd($user);
        
    }
    

    public function find_user_id(Request $request){
        $user = User::where('phone' , $request->phone)->first();
        
        if (isset($user->id)) {
            echo("사용자 아이디는 ".$user->user_id." 입니다.");       
        }else{
            echo("등록되지 않은 연락처 입니다.");       
        }
    }

    public function list(Request $request){
        $start_no = $request->start_no;
        $row = $request->row;
        
        $rows = User::where('id' ,">=", $start_no)->where('user_type','0')->orderBy('id', 'desc')->orderBy('id')->limit($row)->get();

        $list = new \stdClass;

        $list->status = "200";
        $list->msg = "success";
        $list->cnt = count($rows);
        $list->data = $rows;
        
        echo(json_encode($list));
        
    }


}