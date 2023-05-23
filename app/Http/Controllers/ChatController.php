<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\GetChatRequest;
use App\Http\Requests\StoreChatRequest;
use App\Models\Chat;

class ChatController extends Controller
{
    /**
     * Display a listing of the chats.
     */
    public function index(GetChatRequest $request)
    {
        $data = $request->validated();
        $is_private = 1;
        if ($request->has('is_private'))
        {
            $is_private = (int)$data['is_private'];
        }
        // get collection of chat based on some query
        // 1- collect all chat based on private or no
        // 2- only if message relation method have value
        // 3- apply lastMessage relation and its user == apply participants relation and its user
        $userId = auth()->user()->id;

        $chats = Chat::where('is_private',$is_private)
        // detect only chats that auth user created {scope chats by user id}
            ->HasParticipant($userId)
            ->whereHas('participants')
            ->whereHas('message')
            // الرساله وصاحبها والاعضاء ومعلومات الاعضاء
            ->with('lastMessage.user','participants.user')
            ->latest('updated_at')
            ->get();
        return $this->success($chats);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreChatRequest $request)
    {
        $data = $this->prepareStoreData($request);
        if($data['user_id'] === $data['otherUser_id'])
        {
            return $this->error("you chan't create chat with your own");
        }

        // if user have previous chat with this other this user
        $previousChat = $this->getPreviousChat($data['otherUser_id']);

        if($previousChat === null)
        {
            $chat = Chat::create($data['data']);
            $chat->participants()->createMany(
                [
                    ['user_id' => $data['user_id']],
                    ['user_id' => $data['otherUser_id']]
                ]);

            $chat->refresh()->load('lastMessage.user','participants.user');
            return $this-> success($chat);
        }
        return $this->success($previousChat->load('lastMessage.user','participants.user'));
    }

    /**
     * return list of chat participant my id and other id and is private or no
     *
     * @param StoreChatRequest $request
     * @return void
     */
    private function prepareStoreData(StoreChatRequest $request)
    {
        $data = $request->validated();
        $otherUserId = (int)$data['user_id'];
        unset($data['user_id']);
        $data['created_by'] = auth()->user()->id;

        return [
            'otherUser_id' => $otherUserId,
            'user_id' => auth()->user()->id,
            'data' => $data,
        ];
    }

    private function getPreviousChat(int $otherUserId)
    {
        $userId = auth()->user()->id;
        return Chat::where('is_private',1)
        ->whereHas('participants',
        function($query) use ($userId)
        {
            $query->where('user_id',$userId);
        })
        ->whereHas('participants',
        function($query) use ($otherUserId)
        {
            $query->where('user_id',$otherUserId);

        })
        ->first();
    }

    /**
     * get single chat
     */
    public function show(Chat $chat)
    {
        $chat->load('lastMessage.user','participants.user');
        return $this->success($chat);
    }









    // /**
    //  * Update the specified resource in storage.
    //  */
    // public function update(Request $request, string $id)
    // {
    //     //
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  */
    // public function destroy(string $id)
    // {
    //     //
    // }
}
