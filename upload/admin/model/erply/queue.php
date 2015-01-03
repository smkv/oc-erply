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

    public function add($erply_product_id , $name , $group , $seria){
        $this->db->query("INSERT INTO  product_queue (erply_product_id , erply_product_name, erply_product_group , erply_product_seria) VALUES('".$this->db->escape($erply_product_id)."','".$this->db->escape($name)."','".$this->db->escape($group)."','".$this->db->escape($seria)."')");
        return $this->db->getLastId();
    }
    public function remove($erply_product_id){
        $this->db->query("DELETE FROM  product_queue WHERE erply_product_id = '".$this->db->escape($erply_product_id)."'");
    }
}