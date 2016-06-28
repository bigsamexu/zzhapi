<?php

class CommonController extends Yaf\Controller_Abstract {
    public $userinfo;
    public $output_data;
    public $limit;

    public function post($key, $filter = true) {
        if ($filter) {
            return filterStr($this->getRequest()->getPost($key));
        } else {
            return $this->getRequest()->getPost($key);
        }
    }

    public function getParam($key, $filter = true) {
        if ($this->getRequest()->isGet()) {
            if ($filter) {
                return filterStr($this->getRequest()->get($key));
            } else {
                return $this->getRequest()->get($key);
            }
        } else {
            if ($filter) {
                return filterStr($this->getRequest()->getPost($key));
            } else {
                return $this->getRequest()->getPost($key);
            }
        }
    }

    public function getQuery($key, $filter = true) {
        if ($filter) {
            return filterStr($this->getRequest()->getQuery($key));
        } else {
            return $this->getRequest()->getQuery($key);
        }
    }

    public function init() {
        /**
         * 项目是后台管理项目，所以必须要进行的一个操作就是验证是否登录，如果没有登录的话就进行登录操作
         * 登录的控制器是Login/Index地方。
         */
        $this->userinfo = getSession('userinfo');
        if (!$this->userinfo && !$this->noLoginAction()) {
            $this->redirect(base_url('/Public/Login'));
        }
        $this->output_data['userinfo'] = $this->userinfo;
        $this->output_data['menus']    = $this->allowGetMenu() ? $this->getMenus() : [];
        $this->output_data['limit']    = $this->limit = 15;
    }

    public function noLoginAction() {
        $no_login_action = array(
            'Public' => array('login', 'verify', 'register','test'),
            'Page'   => array('share')
        );
        $request         = $this->getRequest();
        $controller      = $request->controller;
        $action          = $request->action;

        return (key_exists($controller, $no_login_action) && in_array($action, $no_login_action[$controller]));
    }

    public function allowGetMenu() {
        $allow_get_menu_action = [
            'Page/show',
            'Item/show',
            'Page/share'
        ];
        $request               = $this->getRequest();
        $controller            = $request->controller;
        $action                = $request->action;

        return in_array(ucfirst($controller) . '/' . $action, $allow_get_menu_action);
    }

    public function getMenus() {
        $item_id = $this->get('item_id');
        if (!$item_id) {
            return [];
        }
        $item_menu_service = $this->loadService('ItemMenu');
        $result_menu       = $item_menu_service->getMenuByItemId($item_id);
        array_unshift($result_menu, [
            'url'  => base_url('Item/show/item_id/' . $item_id),
            'name' => '目录管理',
            'icon' => 'home',
            'subs' => []
        ]);
        $item_service = $this->loadService('Item');
        if ($item_service->isMeItem($this->userinfo['uid'], $item_id)) {
            array_push($result_menu, [
                'url'  => base_url('Page/add/item_id/' . $item_id),
                'name' => '添加页面',
                'icon' => 'plus',
                'subs' => []
            ]);
        }

        //获取项目的左侧栏目
        return $result_menu;
    }

    public function get($key, $filter = true) {
        if ($filter) {
            return filterStr($this->getRequest()->get($key));
        } else {
            return $this->getRequest()->get($key);
        }
    }

    protected function loadService($service_name) {
        try {
            Yaf\Loader::import('CommonService.class.php');
            $service_name = ucfirst($service_name);
            static $services;
            if (isset($services[$service_name]) && $services[$service_name]) {
                return $services[$service_name];
            }
            $file = SERVICE_PATH . '/' . $service_name . 'Service.class.php';
            if (PHP_OS == 'Linux') {
                Yaf\Loader::import($file);
            } else {
                require_once $file;
            }
            $class                   = "\\Yboard\\" . $service_name . 'Service';
            $service                 = new $class();
            $services[$service_name] = $service;

            return $service;
        } catch (\Yaf\Exception $e) {
            E($e->getMessage());
        }
    }


    public function offset_format($total, $limit, $offset) {
        $total_page = ceil($total / $limit);//总页数
        $pre_page   = intval($offset / $limit);
        if ($pre_page > ($total_page - 1)) {
            $pre_page = $total_page - 1;
        }
        ($pre_page < 0) && $pre_page = 0;

        return $pre_page;
    }

    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param string $message 错误信息
     * @param string $jumpUrl 页面跳转地址
     * @param mixed  $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @return void
     */
    protected function error($message = '', $jumpUrl = '') {
        $data = array('code' => 0, 'data' => [], 'message' => $message, 'url' => $jumpUrl);
        echo json_encode($data, true);
        exit;
    }

    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param string $message 提示信息
     * @param string $jumpUrl 页面跳转地址
     * @param mixed  $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @return void
     */
    protected function success($data = [], $message = '', $jumpUrl = '') {
        $data = array('code' => 200, 'data' => $data, 'message' => $message, 'url' => $jumpUrl);
        echo json_encode($data, true);
        exit;
    }

    /**
     * 这是返回table数据的方法，用的bsTable来做的，特定的规则。
     * @param $rows     数组
     * @param $total    总条数
     */
    protected function ajaxRows($rows, $total) {
        echo json_encode(array('total' => $total, 'rows' => $rows));
        exit;
    }

}