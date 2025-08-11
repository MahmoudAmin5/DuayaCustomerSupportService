<?php

namespace App\Http\Controllers;
use App\Models\Chat;

use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function index()
    {
        $chats = Chat::with('customer')->whereNotNull('agent_id')->get();
        return view('agent', compact('chats'));
    }
    
}
