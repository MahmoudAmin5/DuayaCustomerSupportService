<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use App\Repositories\Interfaces\ChatRepositoryInterface;
use App\Repositories\Interfaces\MessageRepositoryInterface;
class ChatService{
    protected $_chatRepo;
    protected $_messageRepo;

    public function __construct(ChatRepositoryInterface $chatRepo, MessageRepositoryInterface $messageRepo)
    {
        $this->_chatRepo = $chatRepo;
        $this->_messageRepo = $messageRepo;
    }

    public function StartChat(Request $request){
        $customer = User::findOrCreateChat(
            ['phone'=> $request->input('phone')],
            ['name' => $request->input('name')],
            ['role' => 'customer']
        );
        
    }
}
