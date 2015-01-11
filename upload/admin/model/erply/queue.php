<?php

/**
 * Class ModelErplyQueue
 * @property DB db
 */
class ModelErplyQueue extends Model{

    public function getQueue($start , $limit ,$filter = null){
        $where = "";
        if(!empty($filter)){
            if(is_numeric($filter)){
                $filter = intval($filter);
                $where = "WHERE  p.erply_product_id = $filter OR p.erply_product_ean = '$filter'";
            }else{
                $filter = explode(' ' , trim($filter));
                $fields = array("erply_product_ean","erply_product_name","erply_product_group" , "erply_product_seria");

                $tmp = array();
                foreach($fields as $field){
                    $tmp2 = array();
                    foreach($filter as $value) {
                        $value= $this->db->escape($value);
                        $tmp2[] = "p.$field LIKE '%$value%'";
                    }
                    $tmp[] = implode(' AND ' , $tmp2);
                }
                $where = "WHERE (" . implode(') OR (' , $tmp) .")";
            }
        }

        $query = $this->db->query("SELECT * FROM product_queue p $where  ORDER BY p.id ASC LIMIT $start , $limit");
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

    public function add($erply_product_id , $ean,  $name , $group , $seria){
        $this->db->query("INSERT INTO  product_queue (erply_product_id , erply_product_name, erply_product_group , erply_product_seria, erply_product_ean) VALUES('".$this->db->escape($erply_product_id)."','".$this->db->escape($name)."','".$this->db->escape($group)."','".$this->db->escape($seria)."','".$this->db->escape($ean)."')");
        return $this->db->getLastId();
    }
    public function remove($erply_product_id){
        $this->db->query("DELETE FROM  product_queue WHERE erply_product_id = '".$this->db->escape($erply_product_id)."'");
    }
}