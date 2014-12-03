CREATE TABLE IF NOT EXISTS product_queue (
  id INT PRIMARY KEY,
  product_id INT UNIQUE ,
  erply_product_id INT UNIQUE
)