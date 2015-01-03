<?php
/**
 * @property ModelErplyErply model_erply_erply
 * @property ModelCatalogProduct model_catalog_product
 * @property Request request
 * @property Response response
 * @property Url url
 */
class ControllerErplySync extends Controller{
    public function index() {

        $this->load->model('erply/erply');
        $this->load->model('catalog/product');
//        $allProducts = $this->model_erply_erply->getAllProducts();
//        foreach ($allProducts as $product) {
//            $this->model_catalog_product->getProducts(array());
//        }

        $this->response->setOutput("");
    }
}