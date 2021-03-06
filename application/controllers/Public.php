<?php

/**
 *
 * @filename    Public.php
 * @author      zouzehua<zzh787272581@163.com>
 * @version     $Id$
 * @since        1.0
 * @time        2016/4/12 22:53
 */
class PublicController extends CommonController
{
    public function loginAction()
    {
        if (IS_AJAX) {
            $username = $this->post('username', false);
            $password = $this->post('password', false);
            $member_service = $this->loadService('member');

            $ret = $member_service->login($username, $password);
//            $ret = $member_service->ldap_login($username, $password);
            if ($ret['state']) {
                $http_referer = getCookie('referer_page');
                if (!$http_referer) {
                    $http_referer = base_url('Index/index');
                }
                clearCookie('referer_page');
                $this->success([], '登录成功', $http_referer);
            }
            $this->error($ret['message']);
        }
    }

    public function registerAction()
    {
//        $this->error('不开放注册');
        if (IS_AJAX) {
            $username = $this->post('username');
            $password = $this->post('password');
            $confirm_pass = $this->post('confirm_pass');
            $verify = $this->post('verify');
            $member_service = $this->loadService('member');

            $ret = $member_service->register($username, $password, $confirm_pass, $verify);
            if ($ret['state']) {
                $this->success([], '注册成功', base_url('Index/index'));
            }
            $this->error($ret['message']);
        }
    }

    public function verifyAction()
    {
        ob_clean();
        Yaf\Loader::import('Verify.class.php');
        $verify = new \Yboard\Verify([
            'imageW' => 290
        ]);
        $verify->entry(1);

        return false;
    }

    public function loginoutAction()
    {
        clearSession('userinfo');
        $this->success([], '退出成功', base_url('Public/login'));

        return false;
    }

    public function testAction()
    {
        $member_service = $this->loadService('Member');
        $member_service->getInfoById(1);

        return false;
    }
}