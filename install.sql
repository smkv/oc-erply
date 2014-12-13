CREATE TABLE IF NOT EXISTS product_queue (
  id INT PRIMARY KEY AUTO_INCREMENT,
  erply_product_id INT UNIQUE,
  erply_product_name VARCHAR(255)
)