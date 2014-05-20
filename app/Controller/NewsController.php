<?php

/**
 * Created by PhpStorm.
 * User: daobv
 * Date: 5/18/14
 * Time: 1:45 AM
 */
class NewsController extends AppController{
    public $helpers = array('Html', 'Form');

    public function pull(){
        $this->loadModel('News');
        $this->loadModel('Category');
        $this->loadModel('Tags');
        $this->loadModel('Tags_meta');
        $rss = new DOMDocument();
        $rss_url = 'http://vnexpress.net/rss/';
        $category = array(
            'thoi-su',
            'doi-song',
            'the-gioi',
            'kinh-doanh',
            'giai-tri',
            'the-thao',
            'phap-luat',
            'du-lich',
            'khoa-hoc',
            'so-hoa',
        );
        foreach ($category as $rss_slug){
            $rss->load($rss_url . $rss_slug . '.rss');
            foreach ($rss->getElementsByTagName('item') as $node) {
                $item_news = array(
                    'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
                    'desc' => $node->getElementsByTagName('description')->item(0)->nodeValue,
                    'link' => $node->getElementsByTagName('link')->item(0)->nodeValue,
                    'date' => strtotime($node->getElementsByTagName('pubDate')->item(0)->nodeValue),
                );
                $slug = parent::get_slug($item_news['title']);
                $news = $this->News->find('first', array(
                    'conditions' => array('News.slug' => $slug)
                ));
                if (!$news){
                    $file = parent::get_content_by_url($item_news['link']);
                    $file = str_replace('<div class="txt_tag">Tags</div>', '', $file);
                    $contents = parent::get_content_by_tag($file, '<div class="fck_detail width_common">');
                    if ($contents == '') {
                        $contents = parent::get_content_by_tag($file, '<div id="article_content">');
                    }
                    $this->News->create();
                    $item_news['desc'] = parent::fix_desc($item_news['desc'], $item_news['link'], $slug);
                    $item_news['category'] = $this->Category->get_catid_by_slug($rss_slug);
                    $item_news['slug'] = $slug;
                    $item_news['content'] = $contents;
                    $item_news['picture'] = parent::get_picture(parent::get_content_by_tag($item_news['desc'], '<a href="news/view?slug='.$slug.'">'));
                    $item_news['desc'] = parent::remove_picture_in_desc($item_news['desc'], $slug, $item_news['picture']);
                    if($item_news['picture'] != ''){
                        $this->News->save($item_news);
                    }
                    $item_tags_meta['news'] = $this->News->getInsertID();
                    $tag_content = parent::get_content_by_tag($file, '<div class="block_tag width_common space_bottom_20">');
                    $tags = parent::get_tag_by_content($tag_content);
                    foreach($tags as $tag){
                        if(!$this->Tags->is_existed_tag($tag)){
                            $this->Tags->create();
                            $item_tags['tags'] = $tag;
                            $this->Tags->save($item_tags);
                            $item_tags_meta['tags'] = $this->Tags->getInsertID();
                        }
                        else{
                            $item_tags_meta['tags'] =  $this->Tags->get_existed_tag_id($tag);
                        }
                        $this->Tags_meta->create();
                        $this->Tags_meta->save($item_tags_meta);
                    }
                }
            }
        }
    }

    public function view(){
        parent::init();
        $slug = $this->params['slug'];
        $news = $this->News->find('first', array(
            'conditions' => array('News.slug' => $slug)
        ));
        if($news){
            $this->set('news', $news);
            $this->set('slug',$news['Category']['slug']);
        }else{
            throw new NotFoundException(__('Invalid slug'));
        }
    }
}