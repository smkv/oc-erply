<?php

/**
 * Class ModelErplyQueue
 * @property DB db
 */
class ModelErplyQueue extends Model{

    public function getQueue($start , $limit){
        $query = $this->db->query("SELECT * FROM product_queue p  ORDER BY p.id ASC LIMIT $start , $limit");
        return $query->rows;
    }

    public function getQueueSize(){
        $query = $this->db->query("SELECT count(p.id) as total FROM product_queue p");
        return $query->row['total'];
    }

    public function getByErplyProdyctId($productID){
        $query = $this->db->query("SELECT * FROM product_queue p  WHERE p.erply_product_id = " . $this->db->escape($productID));
        return $query->row;
    }

    public function add($id , $name){
        $this->db->query("INSERT INTO  product_queue (erply_product_id , erply_product_name) VALUES('".$this->db->escape($id)."','".$this->db->escape($name)."')");
        return $this->db->getLastId();
    }

}