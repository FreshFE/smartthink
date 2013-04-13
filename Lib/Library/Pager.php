<?php namespace Think\Library;
/**
 * Library/Pager.class.php
 * Smart ThinkPHP
 * Copyright (c) 2004-2013 Methink
 *
 * @copyright     Copyright (c) Methink
 * @link          https://github.com/minowu/extend
 * @package       Library.Pager
 * @since         Smart ThinkPHP Extend 1.0.0
 * @license       Apache License (http://www.apache.org/licenses/LICENSE-2.0)
 */

/**
 * Pager Class
 * 根据每页显示多少条来输出页码样式
 */
class Pager {

    /**
     * 总共有多少行数据，表示所有页面里的该项总共有多少行
     *
     * var int
     */
    public $totalRows;

    /**
     * 列出多少行数据，表示该页码内显示多少行
     * 
     * var int
     */
    public $listRows = 20;

    /**
     * 显示多少个页码，表示页码块显示几个，例如：1-9页
     * 
     * var int
     */
    public $rollPage = 9;

    /**
     * 分页Get值的名字，例如：?page=9
     *
     * var string
     */
    public $varPage = 'page';

    /**
     * 总共有多少页码，由构造函数计算赋值，根据$this->totalRows计算
     *
     * var int
     */
    protected $totalPages;

    /**
     * 当前页数，记录当前是第几页
     *
     * var int
     */
    protected $nowPage;

    /**
     * 输出页码数组
     *
     * @param int $totalRows 总共有多少行数据
     *
     * @return array 页码数组
     */
    public function output(int $totalRows, int $listRows) {

        // 配置总条数和列出条数
        if($totalRows) $this->totalRows = $totalRows;
        if($listRows) $this->listRows = $listRows;

        // 计算总页码和当前页码
        $this->totalPages = ceil($this->totalRows / $this->listRows);
        $this->nowPage = !empty($_GET[$this->varPage]) ? intval($_GET[$this->varPage]) : 1;

        if($this->nowPage < 1) $this->nowPage = 1;
        if($this->nowPage > $this->totalPages) $this->nowPage = $this->totalPages;

        // 输出返回
        return array(

            'totalRows'     => $this->totalRows,                        // 数据总行数
            'totalPages'    => $this->totalPages,                       // 总页码数
            'nowPage'       => $this->nowPage,                          // 当前页码数

            'firstLink'     => $this->getPageUrl(1),                    // 首页链接
            'endLink'       => $this->getPageUrl($this->totalPages),    // 末页链接
            'preLink'       => $this->getPageUrl($this->nowPage - 1),   // 上一页链接
            'nextLink'      => $this->getPageUrl($this->nowPage + 1),   // 下一页链接
            'nowLink'       => $this->getPageUrl($this->nowPage),       // 当前页链接
            'numLink'       => $this->getPageNum()                      // 以当前页计算的页码数组
        );
    }

    /**
     * 根据给定页码数生成页码
     *
     * @param int $page
     *
     * @return string url链接
     */
    private function getPageUrl(int $page) {

        // 生成url
        if(!$this->url) {

            // 解析url是否带有get值
            $url = defined(__SELF__) ? __SELF__ : $_SERVER['REQUEST_URI'];
            $url = $url . (strpos($url, '?') ? '' : '?');

            // 解析url
            $url_parse = parse_url($url);

            // 如果已经带有varPage的值，则先删除并重新生成url
            if(isset($url_parse['query'])) {

                parse_str($url_parse['query'], $params);
                unset($params[$this->varPage]);
                $url = $url_parse['path'] . '?' . http_build_query($params);
            }

            // 保存url到全局，避免再次计算
            $this->url = $url;
            $this->urlParams = $params;
        }

        // 给定的页码值是否溢出页码范围
        if($page < 1) return 0;
        if($page > $this->totalPages) return 0;

        // 生成返回页码链接，计算urlParams观察是否添加&
        return $this->url . ((count($this->urlParams) > 0) ? '&' : '') . $this->varPage . "=" . $page;
    }

    /**
     * 根据当前页码和总共显示几个页码来得到一个页码数组
     *
     * @return array 页码数组
     */
    private function getPageNum() {

        // 全局赋值
        $rollPage = $this->rollPage;
        $totalPages = $this->totalPages;
        $nowPage = $this->nowPage;

        // 显示页码数溢出
        if($rollPage > $totalPages) $rollPage = $totalPages;

        // 总共totalPages，只显示$rollPage
        // $rollPage的一般为$rollPageHalf
        // $nowPage在$rollPageHalf和($totalPages - $rollPageHalf)之间的关系
        $rollPageHalf = ceil($rollPage / 2);
        $nowPageStart;

        // 页码在头几页
        if($nowPage <= $rollPageHalf) {
            $nowPageStart = 1;
        }

        // 页码在尾几页
        elseif($nowPage >= ($totalPages - $rollPageHalf)) {
            $nowPageStart = $totalPages - $rollPage + 1;
        }

        // 页码在中间
        else {
            $nowPageStart = $nowPage - $rollPageHalf;
        }

        for($i = 0; $i < $rollPage; $i++) {

            $page = $nowPageStart + $i;

            if($page == $nowPage)
                $pageNum[$page] = 0;
            else
                $pageNum[$page] = $this->getPageUrl($page);
        }

        return $pageNum;
    }
}