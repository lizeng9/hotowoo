<?php
use Illuminate\Support\Facades\DB;


function format_pagination($data){
    $rst = [
        "total_page" =>$data["last_page"],
        "total_record" =>$data["total"],
        "page" =>$data["current_page"],
        "page_size" =>$data["per_page"],
        "more" => $data["next_page_url"] !== null,
    ];
    return $rst;
}

/**
 * 根据区域code向上获取父级区域
 * @param $area_code
 * @param array $returnArray
 * @return array
 */
function get_area( $area_code , &$returnArray=[] ){
   if($area_code){
       $result = DB::table('areas')->select('*')->where('code',$area_code)->first();
       if( !empty($result) ){
           array_unshift($returnArray , $result);
           if( $result->parent_code ){
               get_area($result->parent_code , $returnArray);
           }
       }
   }
   return $returnArray;
}

/**
 * 依据key数组,过滤data数组
 * @param $data_array
 * @param $key_array
 * @return array
 */
function array_select_by_keys($data_array,$key_array){
    $rst = [];
    foreach ($key_array as $v){
        if(isset($data_array[$v])) $rst[$v]= $data_array[$v];
    }
    return $rst;
}

/**
 * 转化房东认证类型
 * @param int $id
 * @return string
 */
function change_auth_type($id){
    switch ($id){
        case 1:
            return '国内个人';
            break;
        case 2:
            return '国内企业';
            break;
        case 3:
            return '国外个人';
            break;
        case 4:
            return '国外企业';
            break;
        default:
            return '';
            break;
    }
}

/**
 * 获取图片原图想对应的缩略图的url
 * @param $url
 * @param $width
 * @param $height
 * @return string
 */
function get_related_thumb_url($url,$width,$height){
    if(!$url) return "";
    $url = str_replace_first("uploads", "thumbs", $url);
    $info = pathinfo($url);
    $ret = $info["dirname"] . "/" . $info["filename"] . "/{$width}x{$height}." .$info["extension"];
    return $ret;
}


function render_view($filename,$data){
    ob_clean();
    extract($data);
    require __DIR__ . "/../resources/views/$filename";
    ob_end_flush();
}
/**
 * 稀饭图片格式转化
 * @param $src
 * @param int $width
 * @param int $height
 * @return string
 */
function getThumbName($src,$width=0,$height=0){
    $pathInfo = pathinfo($src);
    return 'https;//www.tourscool.com/images/product/'.$pathInfo['filename'] . '_' . $width . '_'.$height.'.' . $pathInfo['extension'];
}

function get_abbrev_str($str,$length){
    if(mb_strlen($str) <= $length) return $str;
    return mb_substr($str,0,$length) . "...";
}
