<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use MongoDB\Client as MongoDB;

class UsersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        $collection = (new MongoDB())->MongoApp->users;
        //get all requested data in a variable
        $user = $collection->findOne(['email' => $request->email]);
        //Success Message
        return [
            'Status' => '200',
            'message' => 'Thanks, you have successfully signup',
            "Mail" => "Email Sended Successfully",
            'user' => $user
        ];
    }
}
