<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // 1. 显示登录页面
    public function showLogin()
    {
        // 如果已经登录过了，直接踢去后台，别让人重复登录
        if (Auth::check()) {
            return redirect('/admin');
        }
        return view('login');
    }

    // 2. 处理登录提交请求
    public function login(Request $request)
    {
        // 验证输入规范
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 🎯 核心防线：Laravel 自动比对邮箱和加密后的密码
        if (Auth::attempt($credentials)) {
            // 登录成功，刷新 Session 通行证
            $request->session()->regenerate();

            // 威风凛凛地送进总控制后台！
            return redirect()->intended('/admin');
        }

        // ❌ 登录失败，把邮箱原路退回，并报个错
        return back()->withErrors([
            'email' => '账号或密码不正确，请重新检查！',
        ])->onlyInput('email');
    }

    // 3. 安全退出登录 (登出)
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
