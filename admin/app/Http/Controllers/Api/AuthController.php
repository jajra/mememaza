<?php
 
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator, Auth, DB;
use App\Components\Api\CommonManager;
class AuthController extends Controller
{
	public function __construct()
    {
	   $this->middleware('auth:api');
    }
	
	public function handlelike(Request $request) {
		$user = auth()->user();
		$userid = $user->id;
		$type_id = $request->type_id;
		$commonManager = CommonManager::getInstance();
		
		$like = $commonManager->handleLikeStatus($userid, $type_id, 1);
		
		return response()->json(['statuscode'=>true, 'like' => $like], 200);
	}
	
	public function comment(Request $request) {
		$user = auth()->user();
		
		$validator = Validator::make($request->all(), [
            'comment' => 'required'
        ]);

        if($validator->fails()){
            return response()->json(['statuscode' => false, 'error' => "Please enter comment"]);
        }
		//sleep(3);
		$postid = $request->postid;
		$comment = $request->comment;
		$obj = CommonManager::getInstance();
		$post = $obj->getPostById($postid, ['id', 'like', 'share', 'view', 'comment']);
		$limit = 100;
		if($post) {
			if(DB::table('comments')->insert(['type_id' => $post->id, 'user_id' => $user->id , 'type' => 1, 'comment' => $comment])) {
				if(DB::table('posts')->where('id', $post->id)->increment('comment')) {
					$post->comment++;
					$post->comments = DB::table('comments')->select(['*'])->where('type_id', $post->id)->where('type', 1)->orderBy('id', 'desc')->limit($limit)->offset(1 * $limit)->get()->toArray(); //$obj->getPostCommentByAttr($post->id, 1);
					return response()->json(['statuscode' => true, 'post' => $post]);
				}
			}
		}
		return response()->json(['statuscode' => false]);
		
	}
}
