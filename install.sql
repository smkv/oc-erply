DROP TABLE product_queue;
CREATE TABLE IF NOT EXISTS product_queue (
  id INT PRIMARY KEY AUTO_INCREMENT,
  erply_product_id INT UNIQUE,
  erply_product_ean VARCHAR(25),
  erply_product_name VARCHAR(255),
  erply_product_group VARCHAR(255),
  erply_product_seria VARCHAR(255)
);


INSERT INTO oc_event (code, `trigger`, action) VALUES ('erply_back_sync' , 'post.admin.product.edit','erply/queue/sync_back');