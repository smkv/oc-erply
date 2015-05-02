<?php

/**
 * @property ModelErplyQueue model_erply_queue
 * @property ModelErplyErply model_erply_erply
 * @property ModelErplyProduct model_erply_product
 * @property ModelCatalogProduct model_catalog_product
 * @property ModelCatalogManufacturer model_catalog_manufacturer
 * @property ModelLocalisationLanguage model_localisation_language
 * @property Request request
 * @property Response response
 * @property Url url
 */
class ControllerErplyQueue extends Controller
{
    public function index($data = array())
    {
        $this->load->language('erply/queue');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('erply/queue');
        $page = isset($this->request->get['page']) ? max(1, intval($this->request->get['page'])) : 1;
        $filter = isset($this->request->get['filter']) ?$this->request->get['filter'] : "";
        $start = ($page - 1) * $this->config->get('config_limit_admin');
        $limit = $this->config->get('config_limit_admin');

        $queue = $this->model_erply_queue->getQueue($start, $limit, $filter);

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_list'] = $this->language->get('text_list');
        $data['text_no_results'] = $this->language->get('text_no_results');

        $data['filter'] = $filter;
        $data['token'] = $this->session->data['token'];
        $data['filter_action'] =  $this->url->link('erply/queue', 'token=' . $this->session->data['token'], 'SSL');

        $data['queue'] = array();
        foreach ($queue as $item) {
            $data['queue'][] = array(
                'erply_product_id' => $item['erply_product_id'],
                'erply_product_ean' => $item['erply_product_ean'],
                'erply_product_name' => $item['erply_product_name'],
                'erply_product_group' => $item['erply_product_group'],
                'erply_product_seria' => $item['erply_product_seria'],
                'add_action' => $this->url->link('erply/queue/add_product', 'token=' . $this->session->data['token'], 'SSL'),
                'skip_action' => $this->url->link('erply/queue/skip_product', 'token=' . $this->session->data['token'], 'SSL'),
            );
        }


        $product_total = $this->model_erply_queue->getQueueSize($filter);
        $pagination = new Pagination();
        $pagination->total = $product_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('erply/queue', 'token=' . $this->session->data['token'] . '&page={page}&filter='.$filter, 'SSL');

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

        $data['check_new'] = $this->url->link('erply/queue/check_new', 'token=' . $this->session->data['token'], 'SSL');


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

        $allProducts = $this->model_erply_erply->getAllProducts();

        foreach ($allProducts as $product) {
            $q = $this->model_erply_queue->getByErplyProdyctId($product->productID);
            if (!$q) {
                $p = $this->model_erply_product->getProductBySKU($product->productID);
                if (!$p) {
                    $this->model_erply_queue->add($product->productID, $product->code2 ,$product->name, $product->groupName, $product->seriesName);
                }
            }
        }

        $_SESSION['success'] = 'Synced';

        $this->index();
    }

    public function skip_product(){
        $this->load->model('erply/queue');
        $erplyProductId = $this->request->post['erply_product_id'];
        $this->model_erply_queue->remove($erplyProductId);
        $this->index();
    }
    public function add_product()
    {
        $this->load->model('erply/queue');
        $this->load->model('erply/erply');
        $this->load->model('catalog/product');
        $this->load->model('catalog/manufacturer');
        $this->load->model('localisation/language');

        $erplyProductId = $this->request->post['erply_product_id'];

        $queueItem = $this->model_erply_queue->getByErplyProdyctId($erplyProductId);
        if (!$queueItem) {
            throw new Exception("Product does not exists in product queue");
        }

        $erplyProduct = $this->model_erply_erply->getProduct($erplyProductId);

        $product = array();
        $product['model'] = $this->getProductModel($erplyProduct);
        $product['ean'] = $erplyProduct->code2;
        $product['sku'] = $erplyProduct->productID;
        $product['upc'] = '';
        $product['mpn'] = '';
        $product['jan'] = '';
        $product['isbn'] = '';
        $product['location'] = '';
        $product['quantity'] = $this->getFreeQuantity($erplyProduct->warehouses);
        $product['minimum'] = 1;
        $product['subtract'] = 1;
        $product['stock_status_id'] = 5;//TODO get default status
        $product['date_available'] = date('Y-m-d');

        if (!empty($erplyProduct->brandName)) {
            $manufacturers = $this->model_catalog_manufacturer->getManufacturers(array('filter_name' => $erplyProduct->brandName));

            if (!$manufacturers) {
                $product['manufacturer_id'] = $this->model_catalog_manufacturer->addManufacturer(array(
                    'name' => $erplyProduct->brandName,
                    'sort_order' => 0,
                    'image' => null,
                    'keyword' => $erplyProduct->brandName,
                    'manufacturer_store' => array(0),
                ));
            } else {
                $product['manufacturer_id'] = $manufacturers[0]['manufacturer_id'];
            }
        } else {
            $product['manufacturer_id'] = null;
        }
        $product['shipping'] = 1;
        $product['points'] = 0;
        $product['price'] = $erplyProduct->price;

        $product['weight'] = $erplyProduct->grossWeight;
        $product['weight_class_id'] = 1;
        $product['length'] = $erplyProduct->length;
        $product['width'] = $erplyProduct->width;
        $product['height'] = $erplyProduct->height;
        $product['length_class_id'] = 2;
        $product['status'] = 1;
        $product['tax_class_id'] = 9;
        $product['sort_order'] = '';
        $product['product_store'] = array(0);

        if (!empty($erplyProduct->images)) {

            $product['image'] = $this->storeImage('erply', $erplyProduct->images[0]->largeURL);
            $product['product_image'] = array();
            foreach (array_shift($erplyProduct->images) as $image) {
                $product['product_image'][] = array(
                    'image' => $this->storeImage('erply', $image->largeURL),
                    'sort_order' => 0
                );
            }
        } else {
            $product['image'] = null;
        }

        $product['product_description'] = array();
        $languages = $this->model_localisation_language->getLanguages();

        $languageCodeMapping = $this->getLanguageMapping();

        foreach ($languages as $language) {
            $lid = $language['language_id'];
            $l = $languageCodeMapping[$language['code']];
            $nameField = "name$l";
            $name = isset($erplyProduct->$nameField) ? $erplyProduct->$nameField: $erplyProduct->name;
            $descField = "description$l";
            $desc = isset($erplyProduct->$descField) ? $erplyProduct->$descField: $erplyProduct->description;

            $product['product_description'][$lid] = array(
                'name' => $name,
                'meta_title' => $name,
                'description' => $desc,
                'meta_description' => $desc,
                'tag' => $this->getTags($erplyProduct),
                'meta_keyword' => $this->getTags($erplyProduct),
            );
        }


        $product_id = $this->model_catalog_product->addProduct($product);
        $this->model_erply_queue->remove($erplyProductId);

        $this->response->redirect($this->url->link('catalog/product/edit', 'token=' . $this->session->data['token'] . '&product_id=' . $product_id, 'SSL'));

    }

    private function getFreeQuantity($warehouses)
    {
        $quantity = 0;
        foreach ($warehouses as $warehouse) {
            $quantity += intval($warehouse->free);
        }
        return $quantity;
    }

    private function storeImage($path, $imageURL)
    {
        $fileName = end(explode('/', $imageURL));
        $destination = DIR_IMAGE . 'catalog/' . $path;
        if (!file_exists($destination)) {
            mkdir($destination, 0777, true);
        }
        $content = file_get_contents($imageURL);
        file_put_contents($destination . '/' . $fileName, $content);
        return 'catalog/' . $path . '/' . $fileName;
    }

    /**
     * @param $erplyProduct
     * @return string
     */
    private function getTags($erplyProduct)
    {
        $tags = array(
            $erplyProduct->brandName,
            $erplyProduct->groupName,
            $erplyProduct->categoryName,
            $erplyProduct->seriesName
        );
        $tags = array_filter($tags, function ($item) {
                return !empty($item);
            });
        return implode(', ', $tags);
    }


    public function sync_back($productId){
        $this->load->model('erply/erply');
        $this->load->model('catalog/product');
        $this->load->model('catalog/manufacturer');
        $this->load->model('localisation/language');

        $product = $this->model_catalog_product->getProduct($productId);

        if(empty($product['sku'])){
            return;
        }

        $data = array();

        $data['productID']= $product['sku'];
        $data['code']= $product['model'];
        if ($product['ean']) {
            $data['code2'] = $product['ean'];
        }

        $languageCodeMapping = $this->getLanguageMapping();
        $languages = $this->model_localisation_language->getLanguages();

        $productDescriptions = $this->model_catalog_product->getProductDescriptions($productId);

        foreach ($languages as $language) {
            $lid = $language['language_id'];
            $l = $languageCodeMapping[$language['code']];
            $productDescription = $productDescriptions[ $lid];

            $data["name$l"] = $productDescription['name'];
            $data["description$l"] = html_entity_decode($productDescription['description']);
        }



        $this->model_erply_erply->saveProduct($data);

        $this->model_erply_erply->deletePictures($product['sku']);
        if (!empty($product['image'])) {
            $this->model_erply_erply->addPicture($product['sku'], DIR_IMAGE . $product['image']);
        }

        $productImages = $this->model_catalog_product->getProductImages($productId);
        foreach ($productImages as $productImage) {
            $this->model_erply_erply->addPicture($product['sku'], DIR_IMAGE . $productImage['image']);
        }


    }

    /**
     * @return array
     */
    private function getLanguageMapping()
    {
        $languageCodeMapping = array(
            'ru' => 'RUS',
            'en' => 'ENG',
            'es' => 'SPA',
            'de' => 'DER',
            'se' => 'SWE',
            'fi' => 'FIN',
            'et' => 'EST',
            'lv' => 'LAT',
            'lt' => 'LIT',
            'gr' => 'GRE',
        );
        return $languageCodeMapping;
    }

    /**
     * @param $erplyProduct
     * @return mixed
     */
    public function getProductModel($erplyProduct)
    {
        if (!empty($erplyProduct->code)) {
            return strtoupper($erplyProduct->code);
        }else if(!empty($erplyProduct->brandName)){
           return !($erplyProduct->brandName) . ' ' . $erplyProduct->productID;
        }else if(!empty($erplyProduct->seriesName)){
           return strtoupper($erplyProduct->seriesName) . ' ' . $erplyProduct->productID;
        }else if(!empty($erplyProduct->categoryName)){
           return strtoupper($erplyProduct->categoryName) . ' ' . $erplyProduct->productID;
        }else{
            return 'PRODUCT '. $erplyProduct->productID;
        }
    }
}