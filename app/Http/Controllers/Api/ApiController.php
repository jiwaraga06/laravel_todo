<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
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
            'password' => $request->password
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'message' => 'Success menambahkan Account !',
            'token' => $token
        ], 200);
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
        $validate = Validator::make($request->all(), [
            "message" => "required",
        ]);
        if ($validate->fails()) {
            return response()->json([
                "message" => "Gagal menambahkan task",
                "error" => $validate,
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
            "isCompleted" => "required",
            "id" => "required"
        ]);
        if ($validate->fails()) {
            return response()->json([
                "message" => "Gagal menambahkan task",
                "error" => $validate,
            ], 400);
        } else {
            Todo::where('id', $request->id)->update([
                "isCompleted" => 1
            ]);
            return response()->json([
                "message" => "Berhasil mengubah task"
            ], 200);
        }
    }
}
