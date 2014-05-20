
<?php

App::uses('Xml', 'Utility');
class ParsesController extends AppController {

    var $feed_url = "http://vnexpress.net/rss/tin-moi-nhat.rss";
    var $rss_item = array();


    function read() {
        $parsed_xml = Xml::build($this->feed_url);

        // xml to array conversion
        $this->rss_item = Xml::toArray($parsed_xml);
        pr($this->rss_item['rss']['channel']['item']);
        $this->set('data', $this->rss_item['rss']['channel']['item']);


    }

}
?>