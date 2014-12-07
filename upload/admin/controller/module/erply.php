<?php
class ControllerModuleErply extends Controller {

    public function index() {
        $data = array();
        $this->load->language('module/erply');

        $this->document->setTitle($this->language->get('heading_title'));
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_edit'] = $this->language->get('text_edit');



        $data['text_url'] = $this->language->get('text_url');
        $data['text_client'] = $this->language->get('text_client');
        $data['text_username'] = $this->language->get('text_username');
        $data['text_password'] = $this->language->get('text_password');

        $data['erply_url'] ='';
        $data['erply_client'] ='';
        $data['erply_username'] ='';
        $data['erply_password'] ='';

        $this->load->model('setting/setting');
        $settings = $this->model_setting_setting->getSetting('erply');
        $data = array_merge($data , $settings);

        //echo 'HI ' . print_r($data , true);

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $this->model_setting_setting->editSetting('erply', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
        }


        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('module/erply', 'token=' . $this->session->data['token'], 'SSL')
        );

        $data['action'] = $this->url->link('module/erply', 'token=' . $this->session->data['token'], 'SSL');

        $data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');


        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('module/erply.tpl', $data));
    }
}