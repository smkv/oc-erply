<?php

/**
 * Class ModelErplyQueue
 * @property DB db
 */
class ModelErplyQueue extends Model{

    public function getQueue(){
        $query = $this->db->query("SELECT * FROM product_queue p  ORDER BY p.id ASC");
        return $query->rows;
    }

    public function getByErplyProdyctId($productID){
        $query = $this->db->query("SELECT * FROM product_queue p  WHERE erply_product_id = " . $this->db->escape($productID));
        return $query->row;
    }

    public function add($id , $name){
        $this->db->query("INSERT INTO  product_queue p (erply_product_id , erply_product_name) VALUES('".$this->db->escape($id)."','".$this->db->escape($name)."')");
        return $this->db->getLastId();
    }

}