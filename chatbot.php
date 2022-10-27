<?php
require_once('/var/app/config/config.php');

/**
 * APIのキーの保存方法  *ばれない方法が、必要
 * ライブ配信用のIDを入力もしくは、所得
 * もくもく会員がコメントを書く時の、合言葉（コマンドなど）
 * 合言葉が検出されたら、会員名簿に、出席簿に、記録。（1回目は、ランクに、リーチ。２回目は、ランク登録） 
 */
#   わがままボヂィー

###################### 厳重注意 !!!! ##############################################################################
$key = API_KEY;   #API キー
#################################### GitHub に、乗せるとき。$keyの値を消す !!!! ###################################

$youtubeId = "H8EPL5TNTnk";  # YouTubeライブ配信用のID   （ライブ配信するたび変わるみたいです）
# ↑ $youtubeIdは、ライブ配信を視聴していないと、エラーになる見たいです。
############## 定義 #######################
$authorChannelId = [];
$channelId = [];
$displayName = [];
$messageText = [];
$publishedAt = [];
$end_comment = [];
$copy = [];
$i = 0;
$j = 0;
####################################
//ChatIdの取得
$search_api = "https://www.googleapis.com/youtube/v3/videos?part=liveStreamingDetails&id=" . $youtubeId . "&key=" . $key;
$search_contents = file_get_contents($search_api);
#echo $search_api;
$search_json = json_decode($search_contents, true);
#var_dump($search_json);
#echo $search_json;
$ChatId = $search_json["items"][0]["liveStreamingDetails"]["activeLiveChatId"];
#echo $ChatId;
//ChatIdからliveChatにアクセス用のURL
$search_api = "https://www.googleapis.com/youtube/v3/liveChat/messages?part=snippet,authorDetails&liveChatId=" . $ChatId . "&key=" . $key;
$next = "";
$url = $search_api;

$command = "@@@";                                       #   仮コマンド
date_default_timezone_set('Asia/Tokyo');                #   東京の時間
#####################################################################################
do{
    if($next){
        $url = $search_api . "&pageToken=" . $next;
    }
    $search_contents = file_get_contents($url);                                                                         #   恐らくココのURLにAPIのリクエストに、制限がある。回数なのか、秒数なのか、不明
    $search_json = json_decode($search_contents,true);                                                                  #   最終的なデータが入っている
    #####################################################################################
    $copy = $search_json["items"];
    #var_dump($copy);
    foreach($copy as $k => $data){                                                                                      #   $data は、チャットの件数    使っていません（使い捨て    メモリの無駄使い）
        #   0回目
        if(empty($messageText[$j])){                                                                                    #   $messageText[]が0あるいは空だったら
            $presence =  strpos($search_json["items"][$j]["snippet"]["textMessageDetails"]["messageText"], $command);   #   $presence   有&無
            if($presence == false){                                                                                     #   コマンド在り
                $authorChannelId[$i] = $search_json["items"][$j]["snippet"]["authorChannelId"];                         #   
                $channelId[$i] = $search_json["items"][$j]["authorDetails"]["channelId"];                               #   
                $displayName[$i] = $search_json["items"][$j]["authorDetails"]["displayName"];                           #   
                $messageText[$i] = $search_json["items"][$i]["snippet"]["textMessageDetails"]["messageText"];           #   
                $publishedAt[$i] = $search_json["items"][$j]["snippet"]["publishedAt"];                                 #   
                $i++;                                                                                                   #   次の新規にコメントした人ように
            }else{                                                                                                      #   コマンド無
                continue;                                                                                               #   コマンド無  ＝  スキップする
            }                                                                                                           #   コマンド無
        }else{      #   1回目   ~                                                                                       #   $messageText[]がある
            $authorChannelId = array_unique($authorChannelId);                                                          #   配列の重複を削除する
            $channelId = array_unique($channelId);
            $displayName = array_unique($displayName);
            $messageText = array_unique($messageText);
            $publishedAt = array_unique($publishedAt);                                                                  #   配列の重複を削除する    
        }                                                                                                               #   $messageText[]がある
        #############################  データベース 一件一件登録する場合
        
        #$j ++;  #   まとめてDBに登録するため用    $jにカウント
    }
    #############################################################################################
    #時間指定してプログラム再開させる。 一応　5分間の停止
    echo "5分間ぐらいの停止<br>";
    sleep(5); #   5分間～10分間ぐらいの停止の予定です（現段階は、５秒．．．だと思います。）
                #    あまり長いと、コメントが消えてしまうかもしれません。（検証はしていません。）

    ############
    $i++;
    echo $i."回目<br>";
}while($i < 2);  #   ２～５回のループ処理の予定（注意！あまり、長いとAPIのキーが、上限に達してしまうかも？）
var_dump($authorChannelId);
var_dump($channelId);
var_dump($displayName);
var_dump($messageText);
var_dump($publishedAt);