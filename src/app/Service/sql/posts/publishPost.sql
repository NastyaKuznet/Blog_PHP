UPDATE posts 
SET publish_date = CURRENT_TIMESTAMP, 
    is_publish = true
WHERE id = :post_id;