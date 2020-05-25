<?php
/**
 * Created by PhpStorm.
 * User: lihe
 * Date: 2017/4/14
 * Time: 上午10:44
 */
namespace MyApp\Models;

use Phalcon\Mvc\Model;
use Phalcon\DI;
use Phalcon\Db;

class Page extends Model{
    
    /**
     * 分页方法
     * @param int $total 总条数
     * @param int $page 当前页
     * @param string $urlFormat URL格式 如： ?page=
     * @param string $onclick = JavascriptSubmit(#page#);
     * @param array $config =array('size'=>20, 'letter'=>3, 'showType'=>'all');
     * @return string
     */
    function page($total = 1, $page = 1, $urlFormat = '?page=', $onclick = '', $config = array())
    {
        // 样式参考 http://getbootstrap.com/components/#pagination-default

        // 样式设定
        $letter = isset($config['letter']) ? $config['letter'] : 3;
        $showType = isset($config['showType']) ? $config['showType'] : 'all';
        $size = isset($config['size']) ? $config['size'] : 10;

        $pre = $next = $after = $before = '';
        $total_pages = ceil($total / $size);

        // 首页
        if ($page == 1) {
            $first_page = "<li class='disabled'><a href='javascript:;' class='disabled'>First</a></li>";
        } else {
            $on = str_replace('#page#', 1, $onclick);
            $first_page = "<li><a data-pjax='' onclick='{$on}' href='#url#1'>First</a></li>";
        }

        // 末页
        if ($page == $total_pages) {
            $last_page = "<li class='disabled'><a href='javascript:;' class='disabled'>Last</a></li>";
        } else {
            $on = str_replace('#page#', $total_pages, $onclick);
            $last_page = "<li><a data-pjax='' onclick='{$on}' href='#url#{$total_pages}'>Last</a></li>";
        }

        // 当前页
        $current = "<li><a data-pajx='' href='javascript:;' class='active'>{$page}<span class='sr-only'>(current)</span></a></li>";

        // 前一页
        if ($page == 1) {
            $pre = "<li class='disabled'><a data-pajx='' href='javascript:;'><span aria-hidden='true'>&laquo;</span><span class='sr-only'>Previous</span></a></li>";
        } else {
            $n = $page - 1;
            $on = str_replace('#page#', $n, $onclick);
            $pre = "<li><a data-pajx onclick='{$on}' href='#url#{$n}'><span aria-hidden='true'>&laquo;</span><span class='sr-only'>Previous</span></a></li>";
        }

        // 后一页
        if ($page == $total_pages) {
            $next = "<li  class='disabled'><a href='javascript:;'><span aria-hidden='true'>&raquo;</span><span class='sr-only'>Next</span></a></li>";
        } else {
            $n = $page + 1;
            $on = str_replace('#page#', $n, $onclick);
            $next = "<li><a data-pajx='' onclick='{$on}' href='#url#{$n}'><span aria-hidden='true'>&raquo;</span><span class='sr-only'>Next</span></a></li>";
        }

        // body页
        for ($i = 0; $i < $letter; $i++) {
            $n = $page - $letter + $i;
            if ($n > 0) {
                $on = str_replace('#page#', $n, $onclick);
                $before .= "<li><a data-pjax='' onclick='{$on}' href='#url#{$n}'>{$n}</a></li>";
            }
        }
        for ($i = 1; $i <= $letter; $i++) {
            $n = $page + $i;
            if ($n <= $total_pages) {
                $on = str_replace('#page#', $n, $onclick);
                $after .= "<li><a data-pjax='' onclick='{$on}' href='#url#{$n}'>{$n}</a></li>";
            }
        }

        // 展示类型
        if ($showType == 'all') {
            $html = $first_page . $pre . $before . $current . $after . $next . $last_page;
        } elseif ($showType == 'pn') {
            $html = $pre . $before . $current . $after . $next;
        } elseif ($showType == 'fl') {
            $html = $first_page . $before . $current . $after . $last_page;
        } else {
            $html = $before . $current . $after;
        }
        if (!$urlFormat) {
            $preg = "/href='#url#[0-9]*'/i";
            $html = preg_replace($preg, "href='javascript:;'", $html);
        }
        $html = '<nav><ul class="pagination">' . str_replace('#url#', $urlFormat, $html) . '</ul></nav>';
        //dd($html);
        return $html;
    }
}