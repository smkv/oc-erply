<?php

class ModelErplyErply extends Model{


    private function getAPI(){
        $this->load->library('eapi');
        $this->load->model('setting/setting');
        $setting = $this->model_setting_setting->getSetting('erply');

        $url = $setting['url'];
        $client = $setting['client'];
        $username = $setting['username'];
        $password = $setting['password'];
        return new EAPI($url , $client , $username , $password);
    }
}