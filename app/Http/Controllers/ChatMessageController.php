<?php

namespace App\Http\Controllers;

use App\Events\NewMessageSent;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use App\Http\Requests\GetMessageRequest;
use App\Http\Requests\StoreMessageRequest;
use App\Models\Chat;
use App\Models\User;

class ChatMessageController extends Controller
{
    public function index(GetMessageRequest $request)
    {
        $data = $request->validated();
        $chat_id = $data['chat_id'];
        $currentPage = $data['page'];
        $pageSize = $data['page_size'] ?? 15;

        $message = ChatMessage::where('chat_id','=',$chat_id)
        ->with('user')
        ->latest('created_at')
        ->simplePaginate(
            $pageSize, // الصفحه هتاخد قد اي
            ['*'], // كام عمود
            'page', // اسم الصفحة
            $currentPage // انا في الصفحة الكام دلوقتي ؟
        );

        return $this->success($message->getCollection());
    }

    public function store(StoreMessageRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->user()->id;

        $chat_message = ChatMessage::create($data);
        $chat_message->load('user');

        /// TODO send broadcast event to pusher and send notification to onesignal services
        $this->sendNotificationToOther($chat_message);
        return $this->success($chat_message,'mussage has been sent successfully');
    }


    private function sendNotificationToOther(ChatMessage $message)
    {
        broadcast(new NewMessageSent($message))->toOthers();

        $user = auth()->user();

        $user_id = $user->id;

        $chat = Chat::where('id',$message->chat_id)
            ->with(['participants'=>function ($query) use ($user_id)
                {
                    $query->where('user_id','!=',$user_id);
                }])
            ->first();

        if (count($chat->participants) > 0 ) {

            $otherUser_id = $chat->participants[0]->user_id;

            $otherUser = User::where('id',$otherUser_id)->first();

            $otherUser->sendNewMessageNotification([
                'messageData'=>[
                    'senderName' => $user->username,
                    'message' => $message->message,
                    'chat_id'=> $message->chat_id
                ]
            ]);
        }
    }

}
