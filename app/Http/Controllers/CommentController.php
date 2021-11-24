<?php

namespace App\Http\Controllers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use MongoDB\Client as MongoDB;
use Throwable;

class CommentController extends Controller
{
    public function create(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required'
        ]);

        try {
            $getToken = $request->bearerToken();
            $decoded = JWT::decode($getToken, new Key("SocialCamp", "HS256"));
            $userID = $decoded->id;

            //DB Connection
            $post_collection = (new MongoDB())->MongoApp->posts;

            //to change token into string from array
            $encode = json_encode($userID);
            $decoded = json_decode($encode, true);
            $str_decode = $decoded['$oid'];

            $comment = $post_collection->findOne(['_id' => new \MongoDB\BSON\ObjectID($id), 'status' => 'public']);

            $private = $post_collection->findOne(['_id' => new \MongoDB\BSON\ObjectID($id), 'status' => 'private']);

            $image = null;

            if ($request->file('image') != null) {
                $image = $request->file('image')->store('post_Attachments');
            }

            if (isset($comment)) {
                $comment_store = $post_collection->updateOne(
                    [
                        '_id' => new \MongoDB\BSON\ObjectID($id),
                    ],
                    [
                        '$push' => ['comments' => [
                            'id' => rand(),
                            'user_id' => $str_decode,
                            'comment' => $request->comment,
                            'image' => $image
                        ]]
                    ]
                );

                if (isset($comment_store)) {
                    return response([
                        'message' => 'Comment Created Succesfully',
                    ]);
                } else {
                    return response([
                        'message' => 'Something Went Wrong While added Comment',
                    ]);
                }
            } elseif (isset($private)) {
                return response([
                    'message' => 'This Post is Private',
                ]);
            } else {
                return response([
                    'message' => 'No Post Found',
                ]);
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }


    public function update(Request $request, $p_id, $c_id)
    {
        try {
            //get token from header and check user id
            $getToken = $request->bearerToken();
            $decoded = JWT::decode($getToken, new Key("SocialCamp", "HS256"));
            $userID = $decoded->id;

            //DB Connection
            $post_collection = (new MongoDB())->MongoApp->posts;

            //to change token into string from array
            $encode = json_encode($userID);
            $decoded = json_decode($encode, true);
            $str_decode = $decoded['$oid'];

            $get_post = $post_collection->findOne(['user_id' => $str_decode, '_id' => new \MongoDB\BSON\ObjectID($p_id)]);

            $comment_exists = $post_collection->findOne([
                '_id' => new \MongoDB\BSON\ObjectID($p_id),
                'comments.id' => (int)$c_id
            ]);
            //check if comment is not match to db
            if ($comment_exists == null) {
                return response([
                    'message' => 'Comment Not Exits',
                ]);
            }

            $image = null;
            if ($request->file('image') != null) {
                $image = $request->file('image')->store('post_Attachments');
            }

            if (isset($get_post)) {
                if ($request->comment != null) {
                    $post_collection->updateOne(
                        ['comments.id' => (int)$c_id],
                        ['$set' => ['comments.$.comment' => $request->comment]]
                    );
                }

                if ($request->comment != null) {
                    $post_collection->updateOne(
                        ['comments.id' => (int)$c_id],
                        ['$set' => ['comments.$.image' => $image]]
                    );
                }

                //message on Successfully
                return response([
                    'Status' => '200',
                    'message' => 'you have successfully Update Comment',
                ], 200);
            } else {
                //message on Unauthorize
                return response([
                    'Status' => '200',
                    'message' => 'you are not Authorize to Update this Comment',
                ], 200);
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    public function delete(Request $request, $p_id, $c_id)
    {
        try {
            //get token from header and check user id
            $getToken = $request->bearerToken();
            $decoded = JWT::decode($getToken, new Key("SocialCamp", "HS256"));
            $userID = $decoded->id;

            //DB Connection
            $post_collection = (new MongoDB())->MongoApp->posts;

            //to change token into string from array
            $encode = json_encode($userID);
            $decoded = json_decode($encode, true);
            $str_decode = $decoded['$oid'];

            //check if the post is available in DB or not
            $get_post = $post_collection->findOne(['user_id' => $str_decode, '_id' => new \MongoDB\BSON\ObjectID($p_id)]);

            $comment_exists = $post_collection->findOne([
                '_id' => new \MongoDB\BSON\ObjectID($p_id),
                'comments.id' => (int)$c_id
            ]);
            //check if comment is not match to db
            if ($comment_exists == null) {
                return response([
                    'message' => 'Comment Not Exits',
                ]);
            }

            //if post is available
            if (isset($get_post)) {
                $post_collection->updateOne(
                    [],
                    ['$pull' => ['comments' => ['id' => (int)$c_id]]],
                    ['multi' => true]
                );

                // $comment->delete();
                return response([
                    'message' => 'Comment has been Deleted',
                ]);
            } else {
                return response([
                    'message' => 'You Unauthorize to Delete Comment',
                ]);
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    public function friend_posts($id)
    {
        //DB Connection
        $posts_collection = (new MongoDB())->MongoApp->posts;

        $f_posts = $posts_collection->findOne(['user_id' => $id, 'status' => 'public']);

        if (empty($f_posts)) {
            return response([
                'This User Have No Post'
            ]);
        }

        if (isset($f_posts)) {
            return response([
                $f_posts
            ]);
        }
    }
}
