<?php
App::uses('Controller', 'Controller');
class AppController extends Controller {
    public $category;
    public function init(){
        $this->loadModel('Category');
        $category = $this->Category->find('all', array(
            'fields' => array('Category.category', 'Category.id', 'Category.order', 'Category.slug'),
        ));
        if($category){
            $this->set('categories', $category);
        }else{
            throw new NotFoundException(__('Invalid slug'));
        }
    }
    protected function fix_desc($desc, $link, $slug){
        $desc = str_replace($link, 'news/view?slug='.$slug, $desc);
        return $desc;
    }

    protected function get_picture($content){
        if($content != ''){
            $doc = new DOMDocument();
            libxml_use_internal_errors(true);
            $doc->loadHTML($content); // loads your html
            $xpath = new DOMXPath($doc);
            $nodelist = $xpath->query("//img"); // find your image
            $node = $nodelist->item(0); // gets the 1st image
            $value = $node->attributes->getNamedItem('src')->nodeValue;
            return $value;
        }
        else{
            return '';
        }
    }

    protected function get_slug($title){
        $title = str_replace("'", "", $title);
        $title = str_replace("?", "", $title);
        $title = str_replace("!", "", $title);
        $title = str_replace(":", "", $title);
        $title = str_replace("‘", "", $title);
        $title = str_replace("’", "", $title);
        $title = str_replace("\\", "", $title);
        $title = str_replace(" ", "-", $title);
        $title = str_replace(",", "-", $title);
        $title = str_replace(".", "-", $title);
        $title = str_replace("&amp;", "-", $title);
        $unicode = array(
            'a'=>'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ|Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'd'=>'đ|Đ',
            'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ|É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'i'=>'í|ì|ỉ|ĩ|ị|Í|Ì|Ỉ|Ĩ|Ị',
            'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ|Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự|Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'y'=>'ý|ỳ|ỷ|ỹ|ỵ|Ý|Ỳ|Ỷ|Ỹ|Ỵ',
        );
        foreach($unicode as $nonUnicode=>$uni){
            $title = preg_replace("/($uni)/i", $nonUnicode, $title);
        }
        return strtolower($title);
    }

    protected static function get_content_by_url($url){
        $content = file_get_contents($url);
        do{
            $content = str_replace("  "," ",$content);
        }while(strpos($content,"  ",0)!==false);
        return $content;
    }

    protected function get_content_by_tag($content, $tag_and_more,$include_tag = true){
        $p = stripos($content,$tag_and_more,0);

        if($p===false) return "";
        $content=substr($content,$p);
        $p = stripos($content," ",0);
        if(abs($p)==0) return "";
        $open_tag = substr($content,0,$p);
        $close_tag = substr($open_tag,0,1)."/".substr($open_tag,1).">";

        $count_inner_tag = 0;
        $p_open_inner_tag = 1;
        $p_close_inner_tag = 0;
        $count=1;
        do{
            $p_open_inner_tag = stripos($content,$open_tag,$p_open_inner_tag);
            $p_close_inner_tag = stripos($content,$close_tag,$p_close_inner_tag);
            $count++;
            if($p_close_inner_tag!==false) $p = $p_close_inner_tag;
            if($p_open_inner_tag!==false){
                if(abs($p_open_inner_tag)<abs($p_close_inner_tag)){
                    $count_inner_tag++;
                    $p_open_inner_tag++;
                }else{
                    $count_inner_tag--;
                    $p_close_inner_tag++;
                }
            }else{
                $count_inner_tag--;
                if($p_close_inner_tag>0) $p_close_inner_tag++;
            }
        }while($count_inner_tag>0);
        if($include_tag)
            return substr($content,0,$p+strlen($close_tag));
        else{
            $content = substr($content,0,$p);
            $p = stripos($content,">",0);
            return substr($content,$p+1);
        }
    }

    protected function get_tag_by_content($content){
        $xmlDoc = new DOMDocument();
        $content = mb_convert_encoding($content, 'HTML-ENTITIES', "UTF-8");
        libxml_use_internal_errors(true);
        $xmlDoc->loadHTML($content);
        libxml_use_internal_errors(false);
        $searchNodes = $xmlDoc->getElementsByTagName("a");
        $tag = array();
        foreach($searchNodes as $k => $v){
            array_push($tag, $xmlDoc->getElementsByTagName("a")->item($k)->nodeValue);//$searchNode->getAttribute('title'));
        }
        return $tag;
    }

    protected function remove_picture_in_desc($desc, $slug, $picture){
        $desc = str_replace('<a href="news/view?slug='.$slug.'">', '', $desc);
        $desc = str_replace('<img width=130 height=100 src="'.$picture.'" >', '', $desc);
        $desc = str_replace('</a>', '', $desc);
        $desc = str_replace('</br>', '', $desc);
        return $desc;
    }
}
