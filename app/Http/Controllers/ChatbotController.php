<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Chatbot;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ChatbotController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('usar-chatbot'), only: ['responder']),
        ];
    }

    public function responder(Request $request)
    {
        
        $pregunta = strtolower(trim($request->input('mensaje')));

        $respuesta = Chatbot::where('pregunta', 'like', "%$pregunta%")->first();

        if ($respuesta) {
            return response()->json(['respuesta' => $respuesta->respuesta]);
        } else {
            return response()->json(['respuesta' => 'Lo siento, no entiendo tu mensaje.']);
        }
    }
}
