<?php

/**
 * @property ModelErplyQueue model_erply_queue
 * @property ModelErplyErply model_erply_erply
 * @property ModelErplyProduct model_erply_product
 * @property ModelCatalogProduct model_catalog_product
 */
class ControllerErplyQueue extends Controller
{
    public function index( $data= array())
    {
        $this->load->language('erply/queue');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('erply/queue');


        $queue = $this->model_erply_queue->getQueue();

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_list'] = $this->language->get('text_list');
        $data['text_no_results'] = $this->language->get('text_no_results');
        $data['queue'] = $queue;


        $page = 1;
        $product_total = count($queue);
        $pagination = new Pagination();
        $pagination->total = $product_total;
        $pagination->page =$page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('erply/queue', 'token=' . $this->session->data['token'] . '&page={page}', 'SSL');

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($product_total - $this->config->get('config_limit_admin'))) ? $product_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $product_total, ceil($product_total / $this->config->get('config_limit_admin')));



        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
        );


        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('erply/queue', 'token=' . $this->session->data['token'], 'SSL')
        );


        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('erply/queue.tpl', $data));

    }

    public function check_new()
    {
        echo "Check new products\n";
        $this->load->model('erply/queue');
        $this->load->model('erply/erply');
        $this->load->model('erply/product');

        echo "Loaded models\n";
        $allProducts = $this->model_erply_erply->getAllProducts();
        echo "Loaded all products \n";
        foreach($allProducts as $product){
            $q = $this->model_erply_queue->getByErplyProdyctId($product->productID);
            if(!$q){
                $p = $this->model_erply_product->getProductBySKU($product->productID);
                if(!$p){
                    $this->model_erply_queue->add($product->productID , $product->name) ;
                }
            }
        }

        $_SESSION['success'] = 'Synced';

        $this->index();
    }
}