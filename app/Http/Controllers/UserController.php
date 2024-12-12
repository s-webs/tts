<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{

    public function index()
    {
        $users = User::all();
        return Inertia::render('Admin/Users/Index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'role' => 'required|in:admin,worker',
            'password' => 'required|min:6', // Валидация пароля
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => bcrypt($request->password), // Хэшируем пароль
        ]);

        return response()->json(['message' => 'Пользователь успешно добавлен.']);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,worker',
        ]);

        $user->update($request->only('name', 'email', 'role')); // Обновляем пользователя

        return response()->json(['message' => 'Пользователь успешно обновлен.']);
    }

    /**
     * Удалить пользователя.
     */
    public function destroy(User $user)
    {
        $user->delete(); // Удаляем пользователя

        return response()->json(['message' => 'Пользователь успешно удален.']);
    }
}
