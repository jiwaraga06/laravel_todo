<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{
    public function register(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ], [
            'name.required' => 'Nama tidak boleh kosong',
            'email.required' => 'Email tidak boleh kosong',
            'email.unique' => 'Email ini sudah di gunakan',
            'password.required' => 'Password tidak boleh kosong',
            'password.min' => 'Password setidaknya memiliki 8 karakter',
        ]);
        if ($validated->fails()) {
            return response()->json([
                'message' => 'Gagal menambahkan Account !',
                'error' => $validated->errors()
            ], 400);
        }
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'message' => 'Success menambahkan Account !',
            'token' => $token
        ], 200);
    }
    public function login(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'email' => 'required|email',
                'password' => 'required',
            ],
            [
                'email.required' => 'Email masih kosong',
                'password.required' => 'Password masih kosong',
            ],
        );
        if ($validate->fails()) {
            return response()->json([
                'message' => 'Login Gagal !',
                'error' => $validate->errors(),
            ], 400);
        }

        $auth = Auth::attempt([
            'email' => $request->email,
            'password' => $request->password,
        ]);

        if (!$auth) {
            return response()->json([
                'message' => 'Email dan Password tidak cocok',
                'alert' => $auth
            ], 401);
        }
        $user = User::where('email', $request->email)->first();
        $token = $user->createToken('token-auth')->plainTextToken;
        return response()->json([
            'message' => 'Berhasil Login',
            'access_token' => $token,
            'user' => $user,
        ]);
    }
    public function getTodo()
    {
        $todo = Todo::all();
        return response()->json([
            "message" => "Data Todo",
            "data" => $todo
        ]);
    }
    public function addTodo(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                "message" => "required",
            ],
            [
                "message.required" => "Pesan tidak boleh kosong"
            ]
        );
        if ($validate->fails()) {
            return response()->json([
                "message" => "Gagal menambahkan task",
                "error" => $validate->errors(),
            ], 400);
        } else {
            Todo::create([
                "message" => $request->message,
                "isCompleted" => 0,
            ]);
            $todo = Todo::where('message', $request->message)->get();
            return response()->json([
                "message" => "Berhasil menambahkan task",
                "todo" => $todo,
            ], 200);
        }
    }
    public function updateTodo(Request $request)
    {
        $validate = Validator::make($request->all(), [
            // "isCompleted" => "required",
            "id" => "required"
        ]);
        if ($validate->fails()) {
            return response()->json([
                "message" => "Task gagal ditambahkan !",
                "error" => $validate->errors(),
            ], 400);
        } else {
            $todo = Todo::where('id', $request->id)->update([
                "isCompleted" => 1
            ]);
            if ($todo) {
                return response()->json([
                    "message" => "Task berhasil di ubah"
                ], 200);
            } else {
                return response()->json([
                    "message" => "Task tidak dapat di temukan"
                ], 401);
            }
        }
    }
    public function deleteTodo(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validate->fails()) {
            return response()->json([
                "message" => "Gagal Menghapus todo",
                "error" => $validate
            ]);
        }
        $todo = Todo::where('id', $request->id)->delete();
        if ($todo) {
            return response()->json([
                "message" => "Task berhasil di hapus"
            ], 200);
        } else {
            return response()->json([
                "message" => "Task tidak dapat di temukan"
            ], 401);
        }
    }
}
